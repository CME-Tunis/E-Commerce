<?php

namespace App\Entity;

use Doctrine\Common\Collections\Collection;
use App\Repository\CategoryRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
#[ORM\Entity(repositoryClass: CategoryRepository::class)]
class Category
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $type = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;

        return $this;
    }
    public function __toString(): string
    {
        return $this->type ?: 'Category'; // Retourne le nom ou une valeur par défaut
    }
    private $produits;

    public function __construct()
    {
        $this->produits = new ArrayCollection();
    }
    public function getProduits(): Collection
    {
        return $this->produits;
    }
}
