<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;

use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;

use Psr\Log\LoggerInterface;

use Doctrine\ORM\EntityManagerInterface;

use App\Entity\Catalogue\Livre;
use App\Entity\Catalogue\Instrument;
use App\Entity\Catalogue\Musique;
use App\Entity\Catalogue\Piste;
use App\Entity\Panier\Panier;
use App\Entity\Compte;

class AdminController extends AbstractController
{
	private EntityManagerInterface $entityManager;
	private LoggerInterface $logger;

	public function __construct(EntityManagerInterface $entityManager, LoggerInterface $logger)
	{
		$this->entityManager = $entityManager;
		$this->logger = $logger;
	}

	#[Route('/admin', name: 'admin')]
	public function admin(Request $request): Response
	{
		$pages = [];
		$pages[] = ["name" => "Musiques", "url" => "adminMusiques", "informations" => "Quantité : " . $this->entityManager->getRepository("App\Entity\Catalogue\Musique")->count([])];
		$pages[] = ["name" => "Livres", "url" => "adminLivres", "informations" => "Quantité : " . $this->entityManager->getRepository("App\Entity\Catalogue\Livre")->count([])];
		$pages[] = ["name" => "Instruments", "url" => "adminInstruments", "informations" => "Quantité : " . $this->entityManager->getRepository("App\Entity\Catalogue\Instrument")->count([])];
		$pages[] = ["name" => "Database", "url" => "adminDatabase", "informations" => "Afficher les tables de la base de données"];
		return $this->render('admin/admin.html.twig', [
			'pages' => $pages,
		]);
	}

	#[Route('/admin/musiques', name: 'adminMusiques')]
	public function adminMusiquesAction(Request $request): Response
	{
		$query = $this->entityManager->createQuery("SELECT a FROM App\Entity\Catalogue\Musique a");
		$articles = $query->getResult();
		return $this->render('admin/admin.musiques.html.twig', [
			'articles' => $articles,
		]);
	}

	#[Route('/admin/livres', name: 'adminLivres')]
	public function adminLivresAction(Request $request): Response
	{
		$query = $this->entityManager->createQuery("SELECT a FROM App\Entity\Catalogue\Livre a");
		$articles = $query->getResult();
		return $this->render('admin/admin.livres.html.twig', [
			'articles' => $articles,
		]);
	}

	#[Route('/admin/musiques/supprimer', name: 'adminMusiquesSupprimer')]
	public function adminMusiquesSupprimerAction(Request $request): Response
	{
		$entityArticle = $this->entityManager->getReference("App\Entity\Catalogue\Article", $request->query->get("id"));
		if ($entityArticle !== null) {
			$this->entityManager->remove($entityArticle);
			$this->entityManager->flush();
		}
		return $this->redirectToRoute("adminMusiques");
	}

	#[Route('/admin/livres/supprimer', name: 'adminLivresSupprimer')]
	public function adminLivresSupprimerAction(Request $request): Response
	{
		$entityArticle = $this->entityManager->getReference("App\Entity\Catalogue\Article", $request->query->get("id"));
		if ($entityArticle !== null) {
			$this->entityManager->remove($entityArticle);
			$this->entityManager->flush();
		}
		return $this->redirectToRoute("adminLivres");
	}

	#[Route('/admin/livres/ajouter', name: 'adminLivresAjouter')]
	public function adminLivresAjouterAction(Request $request): Response
	{
		$entity = new Livre();
		$formBuilder = $this->createFormBuilder($entity);
		$formBuilder->add("titre", TextType::class);
		$formBuilder->add("auteur", TextType::class);
		$formBuilder->add("prix", NumberType::class);
		$formBuilder->add("disponibilite", IntegerType::class);
		$formBuilder->add("image", TextType::class);
		$formBuilder->add("ISBN", TextType::class);
		$formBuilder->add("nbPages", IntegerType::class);
		$formBuilder->add("dateDeParution", TextType::class);
		$formBuilder->add("valider", SubmitType::class);
		// Generate form
		$form = $formBuilder->getForm();

		$form->handleRequest($request);

		if ($form->isSubmitted()) {
			$entity = $form->getData();
			$entity->setId(hexdec(uniqid()));
			$this->entityManager->persist($entity);
			$this->entityManager->flush();
			return $this->redirectToRoute("adminLivres");
		} else {
			return $this->render('admin/admin.form.html.twig', [
				'form' => $form->createView(),
			]);
		}
	}

	#[Route('/admin/musiques/ajouter', name: 'adminMusiquesAjouter')]
	public function adminMusiquesAjouterAction(Request $request): Response
	{
		$entity = new Musique();
		$formBuilder = $this->createFormBuilder($entity);
		$formBuilder->add("titre", TextType::class);
		$formBuilder->add("artiste", TextType::class);
		$formBuilder->add("prix", NumberType::class);
		$formBuilder->add("disponibilite", IntegerType::class);
		$formBuilder->add("image", TextType::class);
		$formBuilder->add("dateDeParution", TextType::class);
		$formBuilder->add("valider", SubmitType::class);
		// Generate form
		$form = $formBuilder->getForm();

		$form->handleRequest($request);

		if ($form->isSubmitted()) {
			$entity = $form->getData();
			$entity->setId(hexdec(uniqid()));
			$this->entityManager->persist($entity);
			$this->entityManager->flush();
			return $this->redirectToRoute("adminMusiques");
		} else {
			return $this->render('admin/admin.form.html.twig', [
				'form' => $form->createView(),
			]);
		}
	}

	#[Route('/admin/livres/modifier', name: 'adminLivresModifier')]
	public function adminLivresModifierAction(Request $request): Response
	{
		$entity = $this->entityManager->getReference("App\Entity\Catalogue\Livre", $request->query->get("id"));
		if ($entity === null)
			$entity = $this->entityManager->getReference("App\Entity\Catalogue\Livre", $request->request->get("id"));
		if ($entity !== null) {
			$formBuilder = $this->createFormBuilder($entity);
			$formBuilder->add("id", HiddenType::class);
			$formBuilder->add("titre", TextType::class);
			$formBuilder->add("auteur", TextType::class);
			$formBuilder->add("prix", NumberType::class);
			$formBuilder->add("disponibilite", IntegerType::class);
			$formBuilder->add("image", TextType::class);
			$formBuilder->add("ISBN", TextType::class);
			$formBuilder->add("nbPages", IntegerType::class);
			$formBuilder->add("dateDeParution", TextType::class);
			$formBuilder->add("valider", SubmitType::class);
			// Generate form
			$form = $formBuilder->getForm();

			$form->handleRequest($request);

			if ($form->isSubmitted()) {
				$entity = $form->getData();
				$this->entityManager->persist($entity);
				$this->entityManager->flush();
				return $this->redirectToRoute("adminLivres");
			} else {
				return $this->render('admin/admin.form.html.twig', [
					'form' => $form->createView(),
				]);
			}
		} else {
			return $this->redirectToRoute("adminLivres");
		}
	}

	#[Route('/admin/musiques/modifier', name: 'adminMusiquesModifier')]
	public function adminMusiquesModifierAction(Request $request): Response
	{
		$entity = $this->entityManager->getReference("App\Entity\Catalogue\Musique", $request->query->get("id"));
		if ($entity === null)
			$entity = $this->entityManager->getReference("App\Entity\Catalogue\Musique", $request->request->get("id"));
		if ($entity !== null) {
			$formBuilder = $this->createFormBuilder($entity);
			$formBuilder->add("id", HiddenType::class);
			$formBuilder->add("titre", TextType::class);
			$formBuilder->add("artiste", TextType::class);
			$formBuilder->add("prix", NumberType::class);
			$formBuilder->add("disponibilite", IntegerType::class);
			$formBuilder->add("image", TextType::class);
			$formBuilder->add("dateDeParution", TextType::class);
			$formBuilder->add("valider", SubmitType::class);
			// Generate form
			$form = $formBuilder->getForm();

			$form->handleRequest($request);

			if ($form->isSubmitted()) {
				$entity = $form->getData();
				$this->entityManager->persist($entity);
				$this->entityManager->flush();
				return $this->redirectToRoute("adminMusiques");
			} else {
				return $this->render('admin/admin.form.html.twig', [
					'form' => $form->createView(),
				]);
			}
		} else {
			return $this->redirectToRoute("adminMusiques");
		}
	}
	#[Route('/admin/instruments', name: 'adminInstruments')]
	public function adminInstrumentsAction(Request $request): Response
	{
		$query = $this->entityManager->createQuery("SELECT a FROM App\Entity\Catalogue\Instrument a");
		$articles = $query->getResult();
		return $this->render('admin/admin.instruments.html.twig', [
			'articles' => $articles,
		]);
	}

	#[Route('/admin/instruments/supprimer', name: 'adminInstrumentsSupprimer')]
	public function adminInstrumentsSupprimerAction(Request $request): Response
	{
		$entityArticle = $this->entityManager->getReference("App\Entity\Catalogue\Article", $request->query->get("id"));
		if ($entityArticle !== null) {
			$this->entityManager->remove($entityArticle);
			$this->entityManager->flush();
		}
		return $this->redirectToRoute("adminInstruments");
	}

	#[Route('/admin/instruments/ajouter', name: 'adminInstrumentsAjouter')]
	public function adminInstrumentsAjouterAction(Request $request): Response
	{
		$entity = new Instrument();
		$formBuilder = $this->createFormBuilder($entity);
		$formBuilder->add("titre", TextType::class);
		$formBuilder->add("marque", TextType::class);
		$formBuilder->add("prix", NumberType::class);
		$formBuilder->add("disponibilite", IntegerType::class);
		$formBuilder->add("image", TextType::class);
		$formBuilder->add("valider", SubmitType::class);
		// Generate form
		$form = $formBuilder->getForm();

		$form->handleRequest($request);

		if ($form->isSubmitted()) {
			$entity = $form->getData();
			$entity->setId(hexdec(uniqid()));
			$this->entityManager->persist($entity);
			$this->entityManager->flush();
			return $this->redirectToRoute("adminInstruments");
		} else {
			return $this->render('admin/admin.form.html.twig', [
				'form' => $form->createView(),
			]);
		}
	}
	#[Route('/admin/instruments/modifier', name: 'adminInstrumentsModifier')]
	public function adminInstrumentsModifierAction(Request $request): Response
	{
		$entity = $this->entityManager->getReference("App\Entity\Catalogue\Instrument", $request->query->get("id"));
		if ($entity === null)
			$entity = $this->entityManager->getReference("App\Entity\Catalogue\Instrument", $request->request->get("id"));
		if ($entity !== null) {
			$formBuilder = $this->createFormBuilder($entity);
			$formBuilder->add("id", HiddenType::class);
			$formBuilder->add("titre", TextType::class);
			$formBuilder->add("marque", TextType::class);
			$formBuilder->add("prix", NumberType::class);
			$formBuilder->add("disponibilite", IntegerType::class);
			$formBuilder->add("image", TextType::class);
			$formBuilder->add("valider", SubmitType::class);
			// Generate form
			$form = $formBuilder->getForm();

			$form->handleRequest($request);

			if ($form->isSubmitted()) {
				$entity = $form->getData();
				$this->entityManager->persist($entity);
				$this->entityManager->flush();
				return $this->redirectToRoute("adminInstruments");
			} else {
				return $this->render('admin/admin.form.html.twig', [
					'form' => $form->createView(),
				]);
			}
		} else {
			return $this->redirectToRoute("adminInstruments");
		}
	}

	#[Route('/admin/database', name: 'adminDatabase')]
	public function adminDatabase(Request $request): Response
	{
		$tables = [Instrument::class, Livre::class, Musique::class, Piste::class, Panier::class, Compte::class];
		$entities = [];
		$i = 0;
		foreach ($tables as $table) {
			$entities[$i] = [];
			$entities[$i]["name"] = $table;
			$entities[$i]["columns"] = "";
			$entities[$i]["count"] = $this->entityManager->getRepository($table)->count([]);
			$columns = $this->entityManager->getClassMetadata($table)->getFieldNames();
			$entities[$i]["columns"] = "";
			foreach ($columns as $column) {
				$entities[$i]["columns"] .= $column . ", ";
			}
			$entities[$i]["columns"] = substr($entities[$i]["columns"], 0, -2);
			$i++;
		}
		return $this->render('admin/admin.database.html.twig', [
			'entities' => $entities,
		]);
	}

	#[Route('/admin/database/{table}', name: 'adminDatabaseTable')]
	public function adminDatabaseTable(Request $request, $table): Response
	{
		$query = $this->entityManager->getRepository($table)->findAll();
		$columns = $this->entityManager->getClassMetadata($table)->getFieldNames();
		$entities = [
			"name" => $table,
			"fields" => array(),
			"data" => array()
		];
		foreach ($columns as $column) {
			if ($column != "password")
			array_push($entities["fields"], $column);
		}
		foreach ($query as $entity) {
			$line = array();
			foreach ($columns as $column) {
				$line[$column] = $entity->{"get" . ucfirst($column)}();
				if (is_array($line[$column]))
					$line[$column] = implode(", ", $line[$column]);
			}
			array_push($entities["data"], $line);
		}
		return $this->render('admin/admin.database.table.html.twig', [
			'entities' => $entities,
		]);
	}

}

