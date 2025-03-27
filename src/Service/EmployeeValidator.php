<?php

namespace App\Service;

use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\Constraints as Assert;

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
}

