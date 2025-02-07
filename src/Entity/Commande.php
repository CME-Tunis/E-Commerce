<?php

namespace App\Entity;
use App\Entity\User;
use App\Enum\StatusCommande;
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

    #[ORM\OneToMany(targetEntity: Panier::class, mappedBy: 'panierCommande')]
    private Collection $commandePanier;

   
    #[ORM\Column(type: 'string', enumType: StatusCommande::class)]
    private StatusCommande $statusCommande;

  

    public function __construct()
    {
        $this->commandePanier = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }
    public function getPaniersForUser(User $user): array
    {
        if ($this->user === $user) {
            return $this->commandePanier->toArray(); // Retourner les paniers si l'utilisateur correspond
        }
    
        return [];
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
     * @return Collection<int, Panier>
     */
    public function getCommandePanier(): Collection
    {
        return $this->commandePanier;
    }

    public function addCommandePanier(Panier $commandePanier): static
    {
        if (!$this->commandePanier->contains($commandePanier)) {
            $this->commandePanier->add($commandePanier);
            $commandePanier->setPanierCommande($this);
        }

        return $this;
    }

    public function removeCommandePanier(Panier $commandePanier): static
    {
        if ($this->commandePanier->removeElement($commandePanier)) {
            // set the owning side to null (unless already changed)
            if ($commandePanier->getPanierCommande() === $this) {
                $commandePanier->setPanierCommande(null);
            }
        }

        return $this;
    }
 
    public function getStatusCommande(): StatusCommande
    {
        return $this->statusCommande;
    }

    public function setStatusCommande(StatusCommande $statusCommande): self
    {
        $this->statusCommande = $statusCommande;

        return $this;
    }

 
}
