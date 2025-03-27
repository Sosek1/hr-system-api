<?php

namespace App\Service;

use App\Entity\Worktime;
use App\Repository\WorktimeRepository;

class WorkSummaryService
{
    private WorktimeRepository $worktimeRepository;
    private const BASE_RATE = 20;
    private const OVERTIME_RATE_MULTIPLIER = 2;
    private const DAILY_HOURS_NORM = 8;
    private const MONTHLY_HOURS_NORM = 40;

    public function __construct(WorktimeRepository $worktimeRepository)
    {
        $this->worktimeRepository = $worktimeRepository;
    }

    public function calculateDailyWorkSummary(string $employeeId, string $date): array
    {
        $dateObj = \DateTime::createFromFormat('d.m.Y', $date);

        $worktime = $this->getWorktimeForDay($employeeId, $dateObj);

        if (!$worktime) {
            return ['error' => ['Brak danych o czasie pracy dla podanej daty.']];
        }

        $workedHours = $this->calculateWorkedHours($worktime);
        $roundedHours = $this->roundHours($workedHours);
        $salaryData = $this->calculateSalaryForDay($roundedHours);

        return $this->formatDailySummaryResponse($salaryData);
    }

    public function calculateMonthlyWorkSummary(string $employeeId, string $month): array
    {
        $formattedMonth = $this->convertMonthFormat($month);

        $dateObj = \DateTime::createFromFormat('Y-m', $formattedMonth);

        $worktimes = $this->getWorktimesForMonth($employeeId, $dateObj);

        if (!$worktimes) {
            return ['error' => ['Brak danych o czasie pracy dla podanego miesiąca.']];
        }

        $totalHours = $this->calculateTotalWorkedHours($worktimes);
        $salaryData = $this->calculateSalaryForMonth($totalHours);

        return $this->formatMonthlySummaryResponse($totalHours, $salaryData);
    }

    private function convertMonthFormat(string $month): ?string
    {
        if (!preg_match('/^(0[1-9]|1[0-2])\.\d{4}$/', $month)) {
            return null;
        }

        [$mm, $yyyy] = explode('.', $month);
        return "{$yyyy}-{$mm}";
    }

    private function getWorktimeForDay(string $employeeId, \DateTime $dateObj): ?Worktime
    {
        $startOfDay = (clone $dateObj)->setTime(0, 0, 0);
        $endOfDay = (clone $dateObj)->setTime(23, 59, 59);

        return $this->worktimeRepository->createQueryBuilder('w')
            ->where('w.employe = :employeeId')
            ->andWhere('w.start_time BETWEEN :start AND :end')
            ->setParameter('employeeId', $employeeId)
            ->setParameter('start', $startOfDay)
            ->setParameter('end', $endOfDay)
            ->getQuery()
            ->getOneOrNullResult();
    }

    private function getWorktimesForMonth(string $employeeId, \DateTime $dateObj): array
    {
        $startOfMonth = (clone $dateObj)->modify('first day of this month')->setTime(0, 0, 0);
        $endOfMonth = (clone $dateObj)->modify('last day of this month')->setTime(23, 59, 59);

        return $this->worktimeRepository->createQueryBuilder('w')
            ->where('w.employe = :employeeId')
            ->andWhere('w.start_time BETWEEN :start AND :end')
            ->setParameter('employeeId', $employeeId)
            ->setParameter('start', $startOfMonth)
            ->setParameter('end', $endOfMonth)
            ->getQuery()
            ->getResult();
    }


    private function calculateWorkedHours(Worktime $worktime): float
    {
        $start = $worktime->getStartTime();
        $end = $worktime->getEndTime();

        return ($end->getTimestamp() - $start->getTimestamp()) / 3600;
    }

    private function roundHours(float $hours): float
    {
        $minutes = ($hours - floor($hours)) * 60;

        if ($minutes <= 15) {
            return floor($hours);
        } elseif ($minutes <= 45) {
            return floor($hours) + 0.5;
        } else {
            return ceil($hours);
        }
    }

    private function calculateTotalWorkedHours(array $worktimes): float
    {
        $totalHours = 0;
        foreach ($worktimes as $worktime) {
            $workedHours = $this->calculateWorkedHours($worktime);
            $totalHours += $this->roundHours($workedHours);
        }
        return $totalHours;
    }

    private function calculateSalaryForDay(float $dailyHours): array
    {
        $regularHours = min($dailyHours, self::DAILY_HOURS_NORM);
        $overtimeHours = max(0, $dailyHours - self::DAILY_HOURS_NORM);

        $regularSalary = $regularHours * self::BASE_RATE;
        $overtimeSalary = $overtimeHours * (self::BASE_RATE * self::OVERTIME_RATE_MULTIPLIER);
        $totalSalary = $regularSalary + $overtimeSalary;

        return [
            "regular_hours" => $regularHours,
            "overtime_hours" => $overtimeHours,
            "total_salary" => $totalSalary
        ];
    }

    private function calculateSalaryForMonth(float $totalHours): array
    {
        $regularHours = min($totalHours, self::MONTHLY_HOURS_NORM);
        $overtimeHours = max(0, $totalHours - self::MONTHLY_HOURS_NORM);

        $regularSalary = $regularHours * self::BASE_RATE;
        $overtimeSalary = $overtimeHours * (self::BASE_RATE * self::OVERTIME_RATE_MULTIPLIER);
        $totalSalary = $regularSalary + $overtimeSalary;

        return [
            "regular_hours" => $regularHours,
            "overtime_hours" => $overtimeHours,
            "total_salary" => $totalSalary
        ];
    }


    private function formatDailySummaryResponse(array $salaryData): array
    {
        return [
            "response" => [
                "regularhours" => $salaryData['regular_hours'],
                "overtimehours" => $salaryData['overtime_hours'],
                "salary_per_hour" => self::BASE_RATE . " PLN",
                "overtime_salary_per_hour" => self::BASE_RATE * self::OVERTIME_RATE_MULTIPLIER . " PLN",
                "total_salary" => "{$salaryData['total_salary']} PLN"
            ]
        ];
    }
    private function formatMonthlySummaryResponse(float $totalHours, array $salaryData): array
    {
        return [
            "response" => [
                "total_hours" => $totalHours,
                "regular_hours" => $salaryData['regular_hours'],
                "overtime_hours" => $salaryData['overtime_hours'],
                "salary_per_hour" => self::BASE_RATE . " PLN",
                "overtime_salary_per_hour" => self::BASE_RATE * self::OVERTIME_RATE_MULTIPLIER . " PLN",
                "total_salary" => "{$salaryData['total_salary']} PLN"
            ]
        ];
    }
}