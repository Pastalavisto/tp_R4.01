<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Psr\Log\LoggerInterface;

use App\Form\CommandeFormType;

use App\Entity\Compte;
use App\Entity\Catalogue\Article;
use App\Entity\Panier\Panier;
use App\Entity\Panier\LignePanier;
use App\Entity\Commande;

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
		$this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
		
		$compte = $this->getUser();
		$article = $this->entityManager->getRepository(Article::class)->findOneBy(["id" => $request->query->get("id")]);
		$panier = $compte->getPanier() ;
		$panier->ajouterLigne($article) ;
		$this->entityManager->flush();
		return $this->render('panier/panier.html.twig', [
            'panier' => $panier,
        ]);
    }
	
    #[Route('/supprimerLigne', name: 'supprimerLigne')]
    public function supprimerLigneAction(Request $request): Response
    {
		$this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
		
		$compte = $this->getUser();
		$panier = $compte->getPanier() ;
		$panier->supprimerLigne($request->query->get("id")) ;
		$this->entityManager->flush();
		if (sizeof($panier->getLignesPanier()) === 0)
			return $this->render('panier/panier.vide.html.twig');
		else
			return $this->render('panier/panier.html.twig', [
				'panier' => $panier,
			]);
    }
	
    #[Route('/recalculerPanier', name: 'recalculerPanier', methods: ["GET", "POST"])]
    public function recalculerPanierAction(Request $request): Response
    {
		$this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
		
		$compte = $this->getUser();
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
		return $this->render('panier/panier.html.twig', [
            'panier' => $panier,
        ]);
    }
	 
    #[Route('/accederAuPanier', name: 'accederAuPanier')]
    public function accederAuPanierAction(Request $request): Response
    {
		$this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
		
		$compte = $this->getUser();
		if (!$compte->getPanier()){
			$panier = $this->entityManager->getRepository(Panier::class)->findOneBy(["compte" => $compte]);
			if (!$panier){
				$panier = new Panier() ;
				$panier->setCompte($compte) ;
				$this->entityManager->persist($panier);
				$compte->setPanier($panier) ;
				$this->entityManager->flush();
			}
		}
		$panier = $compte->getPanier() ;
		if (sizeof($panier->getLignesPanier()) === 0)
			return $this->render('panier/panier.vide.html.twig');
		else
			return $this->render('panier/panier.html.twig', [
				'panier' => $panier,
			]);
    }
	
    #[Route('/commanderPanier', name: 'commanderPanier')]
    public function commanderPanierAction(Request $request): Response
    {
		$this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

		$compte = $this->getUser();
		$panier = $compte->getPanier() ;
		$commande = new Commande();
        $form = $this->createForm(CommandeFormType::class, $commande);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $commande->setCompte($compte);
			$lignesPanier = $panier->getLignesPanier();
			$commande->setLignesPanier($lignesPanier);
			$commande->setAdresse($form->get('adresse')->getData());
			$commande->setPays($form->get('pays')->getData());
			$commande->setVille($form->get('ville')->getData());
			$commande->setCodePostal($form->get('codePostal')->getData());
			$commande->setTotal($panier->getTotal());
			$commande->setDate(new \DateTime());
			$panier->vider();
			$this->entityManager->persist($commande);
			$this->entityManager->flush();
            
			$this->addFlash('success', 'Votre commande a été enregistrée.');
            return $this->redirectToRoute('commande', ['id' => $commande->getId()]);
        }

		return $this->render('commande/commande.panier.html.twig', [
			'commandeForm' => $form->createView(),
			'panier' => $panier,
		]);
    }
}
