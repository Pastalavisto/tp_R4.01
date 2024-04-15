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

	public function __construct(EntityManagerInterface $entityManager, LoggerInterface $logger)
	{
		$this->entityManager = $entityManager;
		$this->logger = $logger;
	}

	#[Route('/compte', name: 'compte')]
	public function compte(Request $request, LoggerInterface $logger): Response
	{
		$this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
		
		$compte = $this->getUser();

		return $this->render('compte/compte.html.twig', [
			'compte' => $compte,
		]);
	}

	#[Route('/compte/commandes', name: 'commandes')]
	public function commandes(Request $request, LoggerInterface $logger): Response
	{
		$this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
		
		$compte = $this->getUser();
		$commandes = $compte->getCommandes();

		return $this->render('compte/compte.commandes.html.twig', [
			'commandes' => $commandes,
		]);
	}
}
