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

use App\Entity\Catalogue\Article;

class ProductController extends AbstractController
{
	private EntityManagerInterface $entityManager;
	private LoggerInterface $logger;
	
	public function __construct(EntityManagerInterface $entityManager, LoggerInterface $logger)  {
		$this->entityManager = $entityManager;
		$this->logger = $logger;
	}

    #[Route('/produit', name: 'produit')]
    public function produit(Request $request, LoggerInterface $logger): Response
    {
		$articles = $this->entityManager->getReference("App\Entity\Catalogue\Article", $request->query->get("id"));
		
		return $this->render('product.html.twig', [
            'articles' => $articles,
        ]);
    }

	public function livreDetailAction($id)
{
    $livre = $this->getDoctrine()
        ->getRepository(Livre::class)
        ->find($id);

    if (!$livre) {
        throw $this->createNotFoundException(
            'Aucun livre trouvé pour l\'id '.$id
        );
    }

    return $this->render('livre/detail.html.twig', ['livre' => $livre]);
}
}
