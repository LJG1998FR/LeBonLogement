<?php

namespace App\Entity;

use App\Repository\BienRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;

#[ORM\Entity(repositoryClass: BienRepository::class)]
class Bien
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $adresse = null;

    #[ORM\Column]
    private ?int $prix = null;

    #[ORM\Column(length: 20)]
    private ?string $superficie = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $date_de_depot = null;

    #[ORM\ManyToOne(inversedBy: 'biens')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Coordonnee $coordonnees = null;

    #[ORM\OneToMany(mappedBy: 'bien', targetEntity: Image::class, orphanRemoval: true)]
    private Collection $images;

    public function __construct()
    {
        $this->images = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAdresse(): ?string
    {
        return $this->adresse;
    }

    public function setAdresse(string $adresse): self
    {
        $this->adresse = $adresse;

        return $this;
    }

    public function getPrix(): ?int
    {
        return $this->prix;
    }

    public function setPrix(int $prix): self
    {
        $this->prix = $prix;

        return $this;
    }

    public function getSuperficie(): ?string
    {
        return $this->superficie;
    }

    public function setSuperficie(string $superficie): self
    {
        $this->superficie = $superficie;

        return $this;
    }

    public function getDateDeDepot(): ?\DateTimeInterface
    {
        return $this->date_de_depot;
    }

    public function setDateDeDepot(\DateTimeInterface $date_de_depot): self
    {
        $this->date_de_depot = $date_de_depot;

        return $this;
    }

    public function getCoordonnees(): ?Coordonnee
    {
        return $this->coordonnees;
    }

    public function setCoordonnees(?Coordonnee $coordonnees): self
    {
        $this->coordonnees = $coordonnees;

        return $this;
    }
    public function __toString(){
        return $this->getId();
    }

    /**
     * @return Collection<int, Image>
     */
    public function getImages(): Collection
    {
        return $this->images;
    }

    public function addImage(Image $image): self
    {
        if (!$this->images->contains($image)) {
            $this->images->add($image);
            $image->setBien($this);
        }

        return $this;
    }

    public function removeImage(Image $image): self
    {
        if ($this->images->removeElement($image)) {
            // set the owning side to null (unless already changed)
            if ($image->getBien() === $this) {
                $image->setBien(null);
            }
        }

        return $this;
    }
}
