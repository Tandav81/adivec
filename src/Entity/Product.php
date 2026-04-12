<?php

namespace App\Entity;

use App\Repository\ProductRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\JoinTable;
use Doctrine\ORM\Mapping\ManyToMany;
use Gedmo\Mapping\Annotation as Gedmo;

#[ORM\Entity(repositoryClass: ProductRepository::class)]
#[ORM\Index(name: 'position_idx', columns: ['position'])]
class Product
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Gedmo\SortablePosition]
    #[ORM\Column]
    private int $position = 0;

    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    #[Gedmo\Slug(fields: ['nom'])]
    #[ORM\Column(length: 191, unique: true, nullable: true)]
    private ?string $slug = null;

    #[ManyToMany(targetEntity: Application::class, inversedBy: 'products')]
    #[JoinTable(name: 'products_application')]
    private Collection $applications;

    #[ORM\ManyToOne(targetEntity: "Type" ,inversedBy: "products")]
    #[ORM\JoinColumn(nullable: false)]
    private ?Type $type = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $image = null;

    /**
     * @var Collection<int, Packaging>
     */
    #[ORM\ManyToMany(targetEntity: Packaging::class)]
    private Collection $packagings;

    #[ORM\ManyToOne(targetEntity: LogoPartenaire::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?LogoPartenaire $fournisseur = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[Gedmo\Timestampable(on: 'create')]
    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $createdAt = null;

    #[Gedmo\Timestampable(on: 'update')]
    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $updatedAt = null;


    public function __construct() {
        $this->applications = new ArrayCollection();
        $this->packagings = new ArrayCollection();
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function setPosition(int $position): void
    {
        $this->position = $position;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): void
    {
        $this->image = $image;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(?string $slug): static
    {
        $this->slug = $slug;

        return $this;
    }

    public function getType(): ?Type
    {
        return $this->type;
    }

    public function setType(Type $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function __toString(): string
    {
        return $this->nom;
    }

    /**
     * @return Collection<int, Product>
     */
    public function getApplications(): Collection
    {
        return $this->applications;
    }

    /**
     * @return Collection<int, Packaging>
     */
    public function getPackagings(): Collection
    {
        return $this->packagings;
    }

    public function addPackaging(Packaging $packaging): static
    {
        if (!$this->packagings->contains($packaging)) {
            $this->packagings->add($packaging);
        }

        return $this;
    }

    public function removePackaging(Packaging $packaging): static
    {
        $this->packagings->removeElement($packaging);

        return $this;
    }

    public function getFournisseur(): ?LogoPartenaire
    {
        return $this->fournisseur;
    }

    public function setFournisseur(?LogoPartenaire $fournisseur): static
    {
        $this->fournisseur = $fournisseur;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }
}
