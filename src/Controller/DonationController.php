<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use App\Entity\Campaign;
use App\Entity\Donation;
use App\Form\DonationType;
use App\Repository\DonationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
final class DonationController extends AbstractController
{
    #[Route('/donation', name: 'app_donation')]
    public function index(DonationRepository $donationRepository): Response
    {
        $donations = $donationRepository->findAll();

        return $this->render('donation/index.html.twig', [
            'donations' => $donations,
        ]);
    }

    #[Route('/donation/new/{campaign}', name: 'app_donation_new')]
    public function new(Request $request, Campaign $campaign, EntityManagerInterface $entityManager): Response
    {
        $donation = new Donation();
        $donation->setCampaign($campaign);
        $donation->setDate(new \DateTime());

        $form = $this->createForm(DonationType::class, $donation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($donation);
            $entityManager->flush();

            // Update campaign current amount
            $campaign->setCurrentAmount($campaign->getCurrentAmount() + $donation->getAmount());
            $entityManager->persist($campaign);
            $entityManager->flush();

            $this->addFlash('success', 'Donation made successfully!');
            return $this->redirectToRoute('app_home');
        }

        return $this->render('donation/new.html.twig', [
            'form' => $form->createView(),
            'campaign' => $campaign,
        ]);
    }

    #[Route('/donation/{id}/edit', name: 'app_donation_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Donation $donation, EntityManagerInterface $entityManager): Response
    {
        $originalAmount = $donation->getAmount();
        $form = $this->createForm(DonationType::class, $donation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Update campaign current amount
            $campaign = $donation->getCampaign();
            $campaign->setCurrentAmount($campaign->getCurrentAmount() - $originalAmount + $donation->getAmount());
            $entityManager->persist($campaign);
            $entityManager->flush();

            $this->addFlash('success', 'Donation updated successfully!');
            return $this->redirectToRoute('app_donation');
        }

        return $this->render('donation/edit.html.twig', [
            'donation' => $donation,
            'form' => $form,
        ]);
    }

    #[Route('/donation/{id}', name: 'app_donation_delete', methods: ['POST'])]
    public function delete(Request $request, Donation $donation, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$donation->getId(), $request->getPayload()->getString('_token'))) {
            // Update campaign current amount
            $campaign = $donation->getCampaign();
            $campaign->setCurrentAmount($campaign->getCurrentAmount() - $donation->getAmount());
            $entityManager->persist($campaign);

            $entityManager->remove($donation);
            $entityManager->flush();

            $this->addFlash('success', 'Donation deleted successfully!');
        }

        return $this->redirectToRoute('app_donation');
    }
}
