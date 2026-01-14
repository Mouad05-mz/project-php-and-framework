<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use App\Repository\DonorRepository;
use Symfony\Component\Routing\Attribute\Route;

final class DonorController extends AbstractController
{
    #[Route('/donors', name: 'app_donor')]
    public function index(DonorRepository $donorRepository): Response
    {
        $donors = $donorRepository->findAll();

        return $this->render('donor/index.html.twig', [
            'donors' => $donors,
        ]);
    }
}
