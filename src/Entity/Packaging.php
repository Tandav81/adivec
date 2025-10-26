<?php

namespace App\Entity;

use App\Repository\PackagingRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PackagingRepository::class)]
class Packaging
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $contenant = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $poids = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getContenant(): ?string
    {
        return $this->contenant;
    }

    public function setContenant(string $contenant): static
    {
        $this->contenant = $contenant;

        return $this;
    }

    public function getPoids(): ?string
    {
        return $this->poids;
    }

    public function setPoids(?string $poids): static
    {
        $this->poids = $poids;

        return $this;
    }

    public function __toString(): string
    {
        $label = $this->contenant;
        if ($this->poids) {
            $label .= ' (' . $this->poids . ')';
        }

        return $label;
    }
}
