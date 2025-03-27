<?php

namespace App\Entity;

use App\Repository\WorktimeRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: WorktimeRepository::class)]
class Worktime
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $start_time = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $end_time = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $start_day = null;

    #[ORM\ManyToOne(inversedBy: 'worktimes')]
    #[ORM\JoinColumn(nullable: false)]
    private ?employee $employe = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStartTime(): ?\DateTimeInterface
    {
        return $this->start_time;
    }

    public function setStartTime(\DateTimeInterface $start_time): static
    {
        $this->start_time = $start_time;

        return $this;
    }

    public function getEndTime(): ?\DateTimeInterface
    {
        return $this->end_time;
    }

    public function setEndTime(\DateTimeInterface $end_time): static
    {
        $this->end_time = $end_time;

        return $this;
    }

    public function getStartDay(): ?\DateTimeInterface
    {
        return $this->start_day;
    }

    public function setStartDay(\DateTimeInterface $start_day): static
    {
        $this->start_day = $start_day;

        return $this;
    }

    public function getEmploye(): ?employee
    {
        return $this->employe;
    }

    public function setEmploye(?employee $employe): static
    {
        $this->employe = $employe;

        return $this;
    }
}
