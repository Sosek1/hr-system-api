<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Worktime;
use App\Entity\Employee;
use App\Service\WorktimeValidator;
use App\Repository\EmployeeRepository;

#[Route('/api', name: 'api_')]
final class WorktimeController extends AbstractController
{
    #[Route('/worktime', name: 'worktime_create', methods:['post'] )]
    public function create(EntityManagerInterface $entityManager, Request $request, WorktimeValidator $validator, EmployeeRepository $employeeRepository): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $requiredFields = ['employee_id', 'start_time', 'end_time'];
        $missingFields = [];

        foreach ($requiredFields as $field) {
            if (!isset($data[$field])) {
                $missingFields[] = $field;
            }
        }

        if (!empty($missingFields)) {
            return new JsonResponse(['error' => ['Podaj brakujące dane:  ' . implode(', ', $missingFields)]], JsonResponse::HTTP_BAD_REQUEST);
        }

        $errors = $validator->validateWorktimeData($data);

        if (!empty($errors)) {
            return new JsonResponse(['error' => $errors], JsonResponse::HTTP_BAD_REQUEST);
        }

        $employee = $employeeRepository->findOneBy(['uuid' => $data['employee_id']]);

        $startTime = \DateTime::createFromFormat('d.m.Y H:i', $data['start_time']);
        $endTime = \DateTime::createFromFormat('d.m.Y H:i', $data['end_time']);

        if (!$startTime || !$endTime) {
            return new JsonResponse(['error' => ['Invalid date format']], JsonResponse::HTTP_BAD_REQUEST);
        }

        $worktime = new Worktime();
        $worktime->setEmploye($employee);
        $worktime->setStartTime($startTime);
        $worktime->setEndTime($endTime);

        $entityManager->persist($worktime);
        $entityManager->flush();

        $worktime_data =  ['response' => ['Czas pracy został dodany!']];

        return $this->json($worktime_data);
    }
}
