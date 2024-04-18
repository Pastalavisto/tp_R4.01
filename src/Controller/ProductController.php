<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Validator\Constraints\Length;

use Psr\Log\LoggerInterface;

use Doctrine\ORM\EntityManagerInterface;

class ProductController extends AbstractController
{
	private EntityManagerInterface $entityManager;
	private LoggerInterface $logger;
	
	public function __construct(EntityManagerInterface $entityManager, LoggerInterface $logger)  {
		$this->entityManager = $entityManager;
		$this->logger = $logger;
	}

    #[Route('/produit/{id}', name: 'produit')]
    public function produit(Request $request, LoggerInterface $logger): Response
    {
		$article = $this->entityManager->getReference("App\Entity\Catalogue\Article", $request->get("id"));
		if (!$article) {
			throw $this->createNotFoundException("L'article n'existe pas");
		}
		$form = $this->createFormBuilder()
			->add('quantite', IntegerType::class, ['required' => true, "constraints" => ["min" => new Length(["min" => 1])], "data" => 1])
			->add('ajouter', SubmitType::class)
			->getForm();
		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid()) {
			$compte = $this->getUser();
			$panier = $compte->getPanier();
			$quantite = $form->get('quantite')->getData();
			$panier->ajouterLigne($article, $quantite);
			$this->entityManager->flush();
			return $this->redirectToRoute("accederAuPanier");
		}
		return $this->render('product.html.twig', [
            'article' => $article,
			'form' => $form->createView()
        ]);
    }
}
