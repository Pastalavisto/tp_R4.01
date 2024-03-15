<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;

use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;

use Psr\Log\LoggerInterface;

use Doctrine\ORM\EntityManagerInterface;

use App\Entity\Compte\Compte;
use App\Entity\Panier\Panier;

class CompteController extends AbstractController
{
	private EntityManagerInterface $entityManager;
	private LoggerInterface $logger;
	
	public function __construct(EntityManagerInterface $entityManager, LoggerInterface $logger)  {
		$this->entityManager = $entityManager;
		$this->logger = $logger;
	}

    #[Route('/connexion', name: 'connexion')]
    public function connexion(Request $request, LoggerInterface $logger): Response
    {
        $session = $request->getSession() ;
        if (!$session->isStarted())
            $session->start() ;
        if ($session->has("compte")){
            return $this->redirectToRoute("compte") ;
        }

        $entity = new Compte() ;
		$formBuilder = $this->createFormBuilder($entity);
		$formBuilder->add("email", EmailType::class) ;
		$formBuilder->add("motDePasse", PasswordType::class) ;
		$formBuilder->add("valider", SubmitType::class) ;
		// Generate form
		$form = $formBuilder->getForm();
		
		$form->handleRequest($request) ;

		$repository = $this->entityManager->getRepository(Compte::class);

		if ($form->isSubmitted()) {
			if ($form->isValid()) {
				$entity = $form->getData() ;
				$compte = $repository->findOneBy(["email" => $entity->getEmail(), "motDePasse" => $entity->getMotDePasse()]) ;
				if ($compte != null){
					$session->set("compte", $compte) ;
					return $this->redirectToRoute("compte") ;
				}
				else {
					return $this->redirectToRoute("creerCompte") ;
				}
			}
		}
		else {
			return $this->render('compte.base.form.html.twig', [
				'form' => $form->createView(),
				'titre' => 'Connexion'
			]);
		}
    }

	#[Route('/inscription', name: 'inscription')]
    public function inscription(Request $request, LoggerInterface $logger): Response {
		$session = $request->getSession() ;
        if (!$session->isStarted())
            $session->start() ;
        if ($session->has("compte")){
            return $this->redirectToRoute("compte") ;
        }

        $entity = new Compte() ;
		$formBuilder = $this->createFormBuilder($entity);
		$formBuilder->add("nom", TextType::class) ;
        $formBuilder->add("prenom", TextType::class) ;
		$formBuilder->add("email", EmailType::class) ;
		$formBuilder->add("motDePasse", PasswordType::class) ;
		$formBuilder->add("valider", SubmitType::class) ;
		// Generate form
		$form = $formBuilder->getForm();
		
		$form->handleRequest($request) ;
		
		if ($form->isSubmitted()) {
			$entity = $form->getData() ;
			$entity->setId(hexdec(uniqid()));
            $panier = new Panier() ;
            $entity->setPanier($panier) ;
			$this->entityManager->persist($entity);
			$this->entityManager->flush();
            $session->set("compte", $entity) ;
			return $this->redirectToRoute("compte") ;
		}
		else {
			return $this->render('compte.base.form.html.twig', [
				'form' => $form->createView(),
				'titre' => 'Créer un compte'
			]);
		}
	}

	#[Route('/compte', name: 'compte')]
    public function compte(Request $request, LoggerInterface $logger): Response {
		$session = $request->getSession() ;
		if (!$session->isStarted())
			$session->start() ;
		if ($session->has("compte")){
			return $this->render('compte.html.twig', [
				'compte' => $session->get("compte"),
			]);
		}
		else {
			return $this->redirectToRoute("connexion") ;
		}
	}

	#[Route('/deconnexion', name: 'deconnexion')]
	public function deconnexion(Request $request, LoggerInterface $logger): Response {
		$session = $request->getSession() ;
		if (!$session->isStarted())
			$session->start() ;
		if ($session->has("compte")){
			$session->remove("compte") ;
		}
		return $this->redirectToRoute("connexion") ;
	}
}
