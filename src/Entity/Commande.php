<?php

namespace App\Entity;

use App\Repository\CommandeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CommandeRepository::class)]
class Commande
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $date = null;

    #[ORM\Column]
    private ?float $prix = null;



    #[ORM\ManyToOne(inversedBy: 'UserCommande')]
    private ?User $user = null;

    #[ORM\ManyToMany(targetEntity: Produit::class, mappedBy: 'panier')]
    private Collection $panier;

    public function __construct()
    {
        $this->produits = new ArrayCollection();
        $this->panier = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): static
    {
        $this->date = $date;

        return $this;
    }

    public function getPrix(): ?float
    {
        return $this->prix;
    }

    public function setPrix(float $prix): static
    {
        $this->prix = $prix;

        return $this;
    }

   

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return Collection<int, Produit>
     */
    public function getPanier(): Collection
    {
        return $this->panier;
    }

    public function addPanier(Produit $panier): static
    {
        if (!$this->panier->contains($panier)) {
            $this->panier->add($panier);
            $panier->addPanier($this);
        }

        return $this;
    }

    public function removePanier(Produit $panier): static
    {
        if ($this->panier->removeElement($panier)) {
            $panier->removePanier($this);
        }

        return $this;
    }
    
}
