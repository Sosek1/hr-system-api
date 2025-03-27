<?php

namespace App\Entity;

use App\Repository\EmployeeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: EmployeeRepository::class)]
class Employee
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 128)]
    private ?string $fullname = null;

    #[ORM\Column(type: 'uuid')]
    private ?Uuid $uuid = null;

    /**
     * @var Collection<int, Worktime>
     */
    #[ORM\OneToMany(targetEntity: Worktime::class, mappedBy: 'employe')]
    private Collection $worktimes;

    public function __construct()
    {
        $this->uuid = Uuid::v4();
        $this->worktimes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFullname(): ?string
    {
        return $this->fullname;
    }

    public function setFullname(string $fullname): static
    {
        $this->fullname = $fullname;

        return $this;
    }

    public function getUuid(): ?Uuid
    {
        return $this->uuid;
    }

    /**
     * @return Collection<int, Worktime>
     */
    public function getWorktimes(): Collection
    {
        return $this->worktimes;
    }

    public function addWorktime(Worktime $worktime): static
    {
        if (!$this->worktimes->contains($worktime)) {
            $this->worktimes->add($worktime);
            $worktime->setEmploye($this);
        }

        return $this;
    }

    public function removeWorktime(Worktime $worktime): static
    {
        if ($this->worktimes->removeElement($worktime)) {
            // set the owning side to null (unless already changed)
            if ($worktime->getEmploye() === $this) {
                $worktime->setEmploye(null);
            }
        }

        return $this;
    }
}
