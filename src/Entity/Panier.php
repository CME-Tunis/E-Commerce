<?php

namespace App\Entity;

use App\Repository\PanierRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PanierRepository::class)]
class Panier
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'commandePanier')]
    private ?Commande $panierCommande = null;

    #[ORM\ManyToOne(inversedBy: 'prodPanier')]
    private ?Produit $panierProd = null;

    #[ORM\Column]
    private ?int $quantite = null;


    #[ORM\Column]
    private ?float $prixTotale = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPanierCommande(): ?Commande
    {
        return $this->panierCommande;
    }

    public function setPanierCommande(?Commande $panierCommande): static
    {
        $this->panierCommande = $panierCommande;

        return $this;
    }

    public function getPanierProd(): ?Produit
    {
        return $this->panierProd;
    }

    public function setPanierProd(?Produit $panierProd): static
    {
        $this->panierProd = $panierProd;

        return $this;
    }

    public function getQuantite(): ?int
    {
        return $this->quantite;
    }

    public function setQuantite(int $quantite): static
    {
        $this->quantite = $quantite;

        return $this;
    }



    public function getPrixTotale(): ?float
    {
        return $this->prixTotale;
    }

    public function setPrixTotale(float $prixTotale): static
    {
        $this->prixTotale = $prixTotale;

        return $this;
    }
    public function calculerPrixTotale(): void
    {
        if ($this->panierProd !== null && $this->quantite !== null) {
            $this->prixTotale = $this->panierProd->getPrix() * $this->quantite;
        }
    }
}
