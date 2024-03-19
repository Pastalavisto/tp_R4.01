<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Psr\Log\LoggerInterface;

use App\Entity\Compte\Compte;
use App\Entity\Catalogue\Article;
use App\Entity\Panier\Panier;
use App\Entity\Panier\LignePanier;

use Doctrine\ORM\EntityManagerInterface;

class PanierController extends AbstractController
{
	private EntityManagerInterface $entityManager;
	private LoggerInterface $logger;
	
	public function __construct(EntityManagerInterface $entityManager, LoggerInterface $logger)  {
		$this->entityManager = $entityManager;
		$this->logger = $logger;
	}
	
    #[Route('/ajouterLigne', name: 'ajouterLigne')]
    public function ajouterLigneAction(Request $request): Response
    {
		$session = $request->getSession() ;
		if (!$session->isStarted())
			$session->start() ;	
		if (!$session->has("idCompte"))
			return $this->redirectToRoute("connexion") ;
		$compte = $this->entityManager->getRepository(Compte::class)->findOneBy(["id" => $session->get("idCompte")]);
		$article = $this->entityManager->getRepository(Article::class)->findOneBy(["id" => $request->query->get("id")]);
		$panier = $compte->getPanier() ;
		$panier->ajouterLigne($article) ;
		$this->entityManager->flush();
		return $this->render('panier.html.twig', [
            'panier' => $panier,
        ]);
    }
	
    #[Route('/supprimerLigne', name: 'supprimerLigne')]
    public function supprimerLigneAction(Request $request): Response
    {
		$session = $request->getSession() ;
		if (!$session->isStarted())
			$session->start() ;	
		if (!$session->has("idCompte"))
			return $this->redirectToRoute("connexion") ;
		$compte = $this->entityManager->getRepository(Compte::class)->findOneBy(["id" => $session->get("idCompte")]);
		$panier = $compte->getPanier() ;
		$panier->supprimerLigne($request->query->get("id")) ;
		$this->entityManager->flush();
		if (sizeof($panier->getLignesPanier()) === 0)
			return $this->render('panier.vide.html.twig');
		else
			return $this->render('panier.html.twig', [
				'panier' => $panier,
			]);
    }
	
    #[Route('/recalculerPanier', name: 'recalculerPanier', methods: ["GET", "POST"])]
    public function recalculerPanierAction(Request $request): Response
    {
		$session = $request->getSession() ;
		if (!$session->has("idCompte"))
			return $this->redirectToRoute("connexion") ;
		$compte = $this->entityManager->getRepository(Compte::class)->findOneBy(["id" => $session->get("idCompte")]);
		$panier = $compte->getPanier() ;
		$it = $panier->getLignesPanier()->getIterator();
		while ($it->valid()) {
			$ligne = $it->current();
			$article = $ligne->getArticle() ;
			$ligne->setQuantite($request->request->all("cart")[$article->getId()]["qty"]);
			$ligne->recalculer() ;
			$it->next();
		}
		$panier->recalculer() ;
		$this->entityManager->flush();
		return $this->render('panier.html.twig', [
            'panier' => $panier,
        ]);
    }
	 
    #[Route('/accederAuPanier', name: 'accederAuPanier')]
    public function accederAuPanierAction(Request $request): Response
    {
		$session = $request->getSession() ;
		if (!$session->has("idCompte"))
			return $this->redirectToRoute("connexion") ;
		$compte = $this->entityManager->getRepository(Compte::class)->findOneBy(["id" => $session->get("idCompte")]);
		if (!$compte->getPanier()){
			$panier = $this->entityManager->getRepository(Panier::class)->findOneBy(["compte" => $compte]);
			$session->get("compte")->setPanier($panier) ;
		}
		$panier = $compte->getPanier() ;
		if (sizeof($panier->getLignesPanier()) === 0)
			return $this->render('panier.vide.html.twig');
		else
			return $this->render('panier.html.twig', [
				'panier' => $panier,
			]);
    }
	
    #[Route('/commanderPanier', name: 'commanderPanier')]
    public function commanderPanierAction(Request $request): Response
    {
		return $this->render('commande.html.twig');
    }
}
