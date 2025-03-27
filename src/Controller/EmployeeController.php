<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Employee;
use App\Service\EmployeeValidator;
use App\Service\WorkSummaryService;

#[Route('/api', name: 'api_')]
final class EmployeeController extends AbstractController
{
    #[Route('/employee', name: 'employee_create', methods:['post'] )]
    public function create(EntityManagerInterface $entityManager, Request $request, EmployeeValidator $validator): JsonResponse
    {

        $data = json_decode($request->getContent(), true);

        $requiredFields = ['name', 'surname'];
        $missingFields = [];

        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                $missingFields[] = $field;
            }
        }

        if (!empty($missingFields)) {
            return new JsonResponse(['error' => 'Podaj brakujące dane:  ' . implode(', ', $missingFields)], JsonResponse::HTTP_BAD_REQUEST);
        }

        $name = $data['name'];
        $surname = $data['surname'];

        $nameErrors = $validator->validateName($name);
        $surnameErrors = $validator->validateSurname($surname);

        if (!empty($nameErrors) || !empty($surnameErrors)) {
            return new JsonResponse(['errors' => array_merge($nameErrors, $surnameErrors)], JsonResponse::HTTP_BAD_REQUEST);
        }

        $employee = new Employee();
        $employee->setFullname($name . " " . $surname);

        $entityManager->persist($employee);
        $entityManager->flush();

        return $this->json([
            'response' => [
                'id' => $employee->getUuid()
            ]
        ]);
    }

    #[Route('/employee/work-summary/daily', name: 'employee_daily_work_summary', methods: ['POST'])]
    public function dailyWorkSummary(Request $request, EntityManagerInterface $entityManager, WorkSummaryService $workSummaryService,  EmployeeValidator $validator): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $date = $data['date'] ?? null;
        $employeeUuid = $data['employee_id'] ?? null;

        $errors = $validator->validateWorkSummaryData($date, $employeeUuid);
        if (!empty($errors)) {
            return new JsonResponse(['error' => $errors], JsonResponse::HTTP_BAD_REQUEST);
        }

        $employee = $entityManager->getRepository(Employee::class)->findOneBy(['uuid' => $employeeUuid]);
        if (!$employee) {
            return new JsonResponse(['error' => ['Nie znaleziono pracownika o podanym unikalnym identyfikatorze']], JsonResponse::HTTP_NOT_FOUND);
        }

        $summary = $workSummaryService->calculateDailyWorkSummary($employee->getId(), $date);

        if (!$summary || empty($summary)) {
            return new JsonResponse(['message' => 'Brak danych o czasie pracy dla podanej daty'], JsonResponse::HTTP_OK);
        }

        return new JsonResponse($summary);
    }
}
