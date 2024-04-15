<?php

namespace App\Entity\Panier;

use ArrayObject;
use App\Entity\Catalogue\Article;
use App\Entity\Compte;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

#[ORM\Entity]
class Panier
{
	#[ORM\Id]
	#[ORM\GeneratedValue(strategy: "AUTO")]
	#[ORM\Column(name: "id")]
	private ?int $id = null;

    private float $total;

	#[ORM\OneToMany(targetEntity: LignePanier::class, mappedBy: "panier", cascade: ["persist", "remove"])]
    private Collection $lignesPanier;

	#[ORM\OneToOne(targetEntity: Compte::class, inversedBy: "panier", cascade: ["persist", "remove"])]
	private ?Compte $compte = null;

	public function getId(): ?int
	{
		return $this->id;
	}

	public function setId(int $id): static
	{
		$this->id = $id;

		return $this;
	}

	public function getCompte(): ?Compte
	{
		return $this->compte;
	}

	public function setCompte(Compte $compte): static
	{
		$this->compte = $compte;
		if ($compte->getPanier() !== $this) {
			$compte->setPanier($this);
		}

		return $this;
	}

	public function __construct()
    {
		$this->lignesPanier = new ArrayCollection();
    }

	public function setTotal(): void
	{
		$this->recalculer();
    }
	
	public function getTotal(): ?float
	{
		$this->recalculer();
		return $this->total;
    }
	
	public function getLignesPanier(): ?Collection
	{
		return $this->lignesPanier;
	}
	
	public function recalculer(): void
	{
		$it = $this->getLignesPanier()->getIterator();
		$this->total = 0.0 ;
		while ($it->valid()) {
			$ligne = $it->current();
			$ligne->recalculer() ;
			$this->total += $ligne->getPrixTotal() ;
			$it->next();
		}
	}
	
	public function ajouterLigne(Article $article): void
	{
		$lp = $this->chercherLignePanier($article) ;
		if ($lp == null) {
			$lp = new LignePanier() ;
			$lp->setArticle($article) ; 
			$lp->setQuantite(1) ;
			$lp->setPanier($this) ;
			$this->lignesPanier->add($lp) ;
		}
		else {
			$lp->setQuantite($lp->getQuantite() + 1) ;
		}
		$this->recalculer() ;
	}
	
	public function chercherLignePanier(Article $article): ?LignePanier
	{
		$lignePanier = null ;
		$it = $this->getLignesPanier()->getIterator();
		while ($it->valid()) {
			$ligne = $it->current();
			if ($ligne->getArticle()->getId() == $article->getId())
				$lignePanier = $ligne ;
			$it->next();
		}
		return $lignePanier ;
	}
	
	public function supprimerLigne(int $id): void
	{
		$existe = false ;
		$it = $this->getLignesPanier()->getIterator();
		while ($it->valid()) {
			$ligne = $it->current();
			if ($ligne->getArticle()->getId() == $id) {
				$existe = true ;
				$key = $it->key();
			}
			$it->next();
		}
		if ($existe) {
			$this->getLignesPanier()->offsetUnset($key);
		}
	}

	public function vider(): void
	{
		foreach ($this->getLignesPanier() as $ligne) {
			$ligne->removePanier();
		}
		$this->lignesPanier = new ArrayCollection();
		$this->recalculer() ;
	}
}

