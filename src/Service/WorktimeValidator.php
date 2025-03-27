<?php

namespace App\Service;

use Symfony\Component\Uid\Uuid;
use App\Repository\WorktimeRepository;
use App\Repository\EmployeeRepository;

class WorktimeValidator
{
    private EmployeeRepository $employeeRepository;
    private WorktimeRepository $worktimeRepository;

    public function __construct(EmployeeRepository $employeeRepository, WorktimeRepository $worktimeRepository)
    {
        $this->employeeRepository = $employeeRepository;
        $this->worktimeRepository = $worktimeRepository;
    }

    public function validateWorktimeData(array $data): array
    {
        $errors = [];

        $employeeUuid = $data['employee_id'] ?? null;
        $startTimeStr = $data['start_time'] ?? null;
        $endTimeStr = $data['end_time'] ?? null;

        if (!Uuid::isValid($employeeUuid)) {
            $errors[] = 'Podany unikalny identyfikator jest niepoprawny';
        }

        $startTime = \DateTime::createFromFormat('d.m.Y H:i', $startTimeStr);
        if (!$startTime || $startTime->format('d.m.Y H:i') !== $startTimeStr) {
            $errors[] = 'Podany czas rozpoczęcia pracy ma niepoprawny format';
        }

        $endTime = \DateTime::createFromFormat('d.m.Y H:i', $endTimeStr);
        if (!$endTime || $endTime->format('d.m.Y H:i') !== $endTimeStr) {
            $errors[] = 'Podany czas zakończenia pracy ma niepoprawny format';
        }

        if (empty($errors)) {
            if (!$this->isStartTimeBeforeEndTime($startTime, $endTime)) {
                $errors[] = 'Czas rozpoczęcia nie może być później niż czas końca pracy';
            }

            if ($this->isWorktimeTooLong($startTime, $endTime)) {
                $errors[] = 'Czas pracy przekroczył 12 godzin';
            }

            $employee = $this->employeeRepository->findOneBy(['uuid' => $employeeUuid]);
            if (!$employee) {
                $errors[] = 'Nie znaleziono pracownika o podanym uuid';
            }

            $startDay = \DateTime::createFromFormat('Y-m-d', $startTime->format('Y-m-d'));
            if ($this->hasWorktimeForDay($employeeUuid, $startDay)) {
                $errors[] = 'Czas pracy dla tego dnia już istnieje';
            }
        }

        return $errors;
    }

    public function isStartTimeBeforeEndTime(\DateTimeInterface $start, \DateTimeInterface $end): bool
    {
        return $start < $end;
    }

    public function isWorkTimeTooLong(\DateTimeInterface $start, \DateTimeInterface $end): bool
    {
        $interval = $start->diff($end);
        return $interval->h >= 12 || ($interval->h === 11 && $interval->i > 0);
    }

    public function hasWorktimeForDay(string $employeeUuid, \DateTimeInterface $startDay): bool
    {
        return (bool) $this->worktimeRepository->createQueryBuilder('w')
            ->innerJoin('w.employe', 'e')
            ->where('e.uuid = :uuid')
            ->andWhere('w.start_day = :startDay')
            ->setParameter('uuid', Uuid::fromString($employeeUuid)->toBinary())
            ->setParameter('startDay', $startDay->format('Y-m-d'))
            ->getQuery()
            ->getScalarResult();
    }
}