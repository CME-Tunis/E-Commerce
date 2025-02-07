<?php

namespace App\Entity;
use App\Enum\Status;
use App\Repository\ProduitRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProduitRepository::class)]
class Produit
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nomP = null;

    #[ORM\Column(length: 255)]
    private ?string $image = null;

    #[ORM\Column(length: 255)]
    private ?string $stock = null;

    #[ORM\Column(length: 255)]
    private ?string $description = null;

    #[ORM\Column(type: 'string', enumType: Status::class)] // Associe l'enum
    private Status $status;

    #[ORM\Column]
    private ?float $prix = null;

    #[ORM\ManyToOne(inversedBy: 'produits')]
    private ?Category $ProdCategory = null;

    #[ORM\OneToMany(targetEntity: Panier::class, mappedBy: 'panierProd')]
    private Collection $prodPanier;



    public function __construct()
    {
        $this->prodPanier = new ArrayCollection();
    }

 

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNomP(): ?string
    {
        return $this->nomP;
    }

    public function setNomP(string $nomP): static
    {
        $this->nomP = $nomP;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(string $image): static
    {
        $this->image = $image;

        return $this;
    }

    public function getStock(): ?string
    {
        return $this->stock;
    }

    public function setStock(string $stock): static
    {
        $this->stock = $stock;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    

    public function getStatus(): Status
    {
        return $this->status;
    }

    public function setStatus(Status $status): self
    {
        $this->status = $status;
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

    public function getProdCategory(): ?Category
    {
        return $this->ProdCategory;
    }

    public function setProdCategory(?Category $ProdCategory): static
    {
        $this->ProdCategory = $ProdCategory;

        return $this;
    }
    public function __toString(): string
    {
        // Retournez une représentation textuelle de l'objet
        return $this->getNomP(); // Par exemple, le nom du produit
    }

    /**
     * @return Collection<int, Panier>
     */
    public function getProdPanier(): Collection
    {
        return $this->prodPanier;
    }

    public function addProdPanier(Panier $prodPanier): static
    {
        if (!$this->prodPanier->contains($prodPanier)) {
            $this->prodPanier->add($prodPanier);
            $prodPanier->setPanierProd($this);
        }

        return $this;
    }

    public function removeProdPanier(Panier $prodPanier): static
    {
        if ($this->prodPanier->removeElement($prodPanier)) {
            // set the owning side to null (unless already changed)
            if ($prodPanier->getPanierProd() === $this) {
                $prodPanier->setPanierProd(null);
            }
        }

        return $this;
    }

  
    
}
