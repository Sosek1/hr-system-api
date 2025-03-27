<?php

namespace App\Service;

use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Uid\Uuid;

class EmployeeValidator
{
    private ValidatorInterface $validator;

    public function __construct(ValidatorInterface $validator)
    {
        $this->validator = $validator;
    }

    public function validateName(string $name): array
    {
        $constraint = new Assert\Regex([
            'pattern' => '/^[A-ZĄĆĘŁŃÓŚŹŻ][a-ząćęłńóśźż]+$/u',
            'message' => 'Invalid name format.'
        ]);

        $violations = $this->validator->validate($name, $constraint);
        return array_map(fn($violation) => $violation->getMessage(), iterator_to_array($violations));
    }

    public function validateSurname(string $surname): array
    {
        $constraint = new Assert\Regex([
            'pattern' => '/^[A-ZĄĆĘŁŃÓŚŹŻ][a-ząćęłńóśźż]+$/u',
            'message' => 'Invalid surname format.'
        ]);

        $violations = $this->validator->validate($surname, $constraint);
        return array_map(fn($violation) => $violation->getMessage(), iterator_to_array($violations));
    }

    public function validateWorkSummaryData(?string $date, ?string $employeeUuid): array
    {
        $errors = [];

        if (!$date) {
            $errors[] = 'Brak wymaganej daty';
        } elseif (!preg_match('/^\d{2}\.\d{2}\.\d{4}$/', $date)) {
            $errors[] = 'Niepoprawny format daty. Oczekiwany: dd.mm.YYYY';
        }

        if (!$employeeUuid) {
            $errors[] = 'Brak unikalnego identyfikatora pracownika';
        } elseif (!Uuid::isValid($employeeUuid)) {
            $errors[] = 'Podany unikalny identyfikator jest niepoprawny';
        }

        return $errors;
    }

    public function validateMonthlyWorkSummaryData(?string $month, ?string $employeeUuid): array
    {
        $errors = [];

        if (!$month) {
            $errors[] = 'Brak wymaganej daty';
        } elseif (!preg_match('/^(0[1-9]|1[0-2])\.\d{4}$/', $month)) {
            $errors[] = 'Niepoprawny format daty. Oczekiwany: mm.YYYY';
        }

        if (!$employeeUuid) {
            $errors[] = 'Brak unikalnego identyfikatora pracownika';
        } elseif (!Uuid::isValid($employeeUuid)) {
            $errors[] = 'Podany unikalny identyfikator jest niepoprawny';
        }

        return $errors;
    }
}

