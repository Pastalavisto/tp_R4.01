<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use App\Entity\Commande;

use Psr\Log\LoggerInterface;

use Doctrine\ORM\EntityManagerInterface;

class CommandeController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private LoggerInterface $logger;

    public function __construct(EntityManagerInterface $entityManager, LoggerInterface $logger)
    {
        $this->entityManager = $entityManager;
        $this->logger = $logger;
    }

    #[Route('/commande/{id}', name: 'commande')]
    public function compte(Request $request, LoggerInterface $logger): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $bought = $request->get('bought');
        $id = $request->get('id');
        $compte = $this->getUser();
        $commande = $this->entityManager->getRepository(Commande::class)->find($id);
        if ($commande->getCompte() != $compte) {
            if ($compte->getRoles() == ['ROLE_ADMIN']) {
                return $this->render('commande/commande.html.twig', [
                    'commande' => $commande,
                    'bought' => $bought,
                ]);
            } else {
                return $this->redirectToRoute('compte');
            }
        }

        return $this->render('commande/commande.html.twig', [
            'commande' => $commande,
            'bought' => $bought,
        ]);
    }
}
