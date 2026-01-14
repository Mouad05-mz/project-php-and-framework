<?php

namespace App\Controller;

use App\Entity\Campaign;
use App\Form\CampaignType;
use App\Repository\CampaignRepository;
use App\Repository\DonationRepository;
use App\Repository\DonorRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class CampaignController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(CampaignRepository $campaignRepository, DonationRepository $donationRepository, DonorRepository $donorRepository): Response
    {
        $campaigns = $campaignRepository->findAll();
        $totalCollected = 0;
        foreach ($campaigns as $campaign) {
            $totalCollected += $campaign->getCurrentAmount();
        }
        $totalDonors = $donorRepository->count([]);
        $totalDonations = $donationRepository->count([]);

        return $this->render('campaign/index.html.twig', [
            'campaigns' => $campaigns,
            'totalCollected' => $totalCollected,
            'totalDonors' => $totalDonors,
            'totalDonations' => $totalDonations,
        ]);
    }

    #[Route('/campaign/new', name: 'app_campaign_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $campaign = new Campaign();
        $form = $this->createForm(CampaignType::class, $campaign);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($campaign);
            $entityManager->flush();

            $this->addFlash('success', 'Campaign created successfully!');
            return $this->redirectToRoute('app_home');
        }

        return $this->render('campaign/new.html.twig', [
            'campaign' => $campaign,
            'form' => $form,
        ]);
    }

    #[Route('/campaign/{id}', name: 'app_campaign_show', methods: ['GET'])]
    public function show(Campaign $campaign): Response
    {
        return $this->render('campaign/show.html.twig', [
            'campaign' => $campaign,
        ]);
    }

    #[Route('/campaign/{id}/edit', name: 'app_campaign_edit', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function edit(Request $request, Campaign $campaign, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(CampaignType::class, $campaign);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Campaign updated successfully!');
            return $this->redirectToRoute('app_campaign_show', ['id' => $campaign->getId()]);
        }

        return $this->render('campaign/edit.html.twig', [
            'campaign' => $campaign,
            'form' => $form,
        ]);
    }

    #[Route('/campaign/{id}', name: 'app_campaign_delete', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function delete(Request $request, Campaign $campaign, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$campaign->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($campaign);
            $entityManager->flush();

            $this->addFlash('success', 'Campaign deleted successfully!');
        }

        return $this->redirectToRoute('app_home');
    }
}
