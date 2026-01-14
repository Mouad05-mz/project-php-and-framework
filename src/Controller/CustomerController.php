<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use App\Repository\CustomerRepository;
use Doctrine\ORM\EntityManagerInterface;

final class CustomerController extends AbstractController
{
    #[Route('/customer', name: 'app_customer')]
    public function index(): Response
    {
        return $this->render('customer/index.html.twig', ['prenom' => 'Yassmina',]);
    }

    #[Route('/customers', name: 'app_customers')]
    public function getAllCustomer(CustomerRepository $customerRepository): Response
    {
        $customers = $customerRepository->findAll();

        return $this->render('customer/listCustomer.html.twig', [
            'customers' => $customers,
        ]);
    }
}
