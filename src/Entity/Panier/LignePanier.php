<?php

namespace App\Entity\Panier;

use App\Entity\Catalogue\Article;
use App\Entity\Panier\Panier;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class LignePanier
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: "AUTO")]
    #[ORM\Column(name: "id")]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Panier::class, inversedBy: "lignesPanier")]
    private Panier $panier;

    #[ORM\ManyToOne(targetEntity: Article::class)]
    private Article $article;

    private ?float $prixUnitaire = null;

    private ?float $prixTotal = null;

    #[ORM\Column(type: "integer")]
    private int $quantite = 1;

    public function __construct()
    {
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): LignePanier
    {
        $this->id = $id;
        return $this;
    }

    public function setPanier(Panier $panier): LignePanier
    {
        $this->panier = $panier;
        return $this;
    }

    public function getPanier(): Panier
    {
        return $this->panier;
    }

    public function setArticle(Article $article): LignePanier
    {
        $this->article = $article;
        $this->setPrixUnitaire($article->getPrix());
        return $this;
    }

    public function getArticle(): Article
    {
        return $this->article;
    }

    public function setPrixUnitaire(float $prixUnitaire): LignePanier
    {
        $this->prixUnitaire = $prixUnitaire;
        $this->recalculer();
        return $this;
    }

    public function getPrixUnitaire(): float
    {
        if ($this->prixUnitaire === null)
            $this->setPrixUnitaire($this->article->getPrix());
        return $this->prixUnitaire;
    }

    public function setPrixTotal(float $prixTotal): LignePanier
    {
        $this->prixTotal = $prixTotal;
        return $this;
    }

    public function getPrixTotal(): ?float
    {
        if ($this->prixTotal === null)
            $this->recalculer();
        return $this->prixTotal;
    }

    public function setQuantite(int $quantite): LignePanier
    {
        $this->quantite = $quantite;
        $this->recalculer();
        return $this;
    }

    public function getQuantite(): ?int
    {
        if ($this->quantite === null)
            $this->setQuantite(1);
        return $this->quantite;
    }

    public function recalculer()
    {
        $this->prixTotal = $this->getQuantite() * $this->getPrixUnitaire();
    }
}

