<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Employee;
use App\Service\EmployeeValidator;

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

        $name = $data['name'] ?? null;
        $surname = $data['surname'] ?? null;

        $nameErrors = $validator->validateName($name);
        $surnameErrors = $validator->validateSurname($surname);

        if (!empty($nameErrors) || !empty($surnameErrors)) {
            return new JsonResponse(['errors' => array_merge($nameErrors, $surnameErrors)], JsonResponse::HTTP_BAD_REQUEST);
        }

        $employee = new Employee();
        $employee->setFullname($name . $surname);

        $entityManager->persist($employee);
        $entityManager->flush();

        return $this->json([
            'response' => [
                'id' => $employee->getUuid()
            ]
        ]);
    }
}
