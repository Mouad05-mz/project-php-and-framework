<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\CampaignRepository;
use App\Repository\DonationRepository;
use App\Repository\DonorRepository;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
final class StatisticsController extends AbstractController
{
    #[Route('/statistics', name: 'app_statistics')]
    public function index(CampaignRepository $campaignRepository, DonationRepository $donationRepository, DonorRepository $donorRepository): Response
    {
        $campaigns = $campaignRepository->findAll();
        $donations = $donationRepository->findAll();
        $donors = $donorRepository->findAll();

        $totalCollected = 0;
        $totalDonations = count($donations);
        $totalDonors = count($donors);
        $totalCampaigns = count($campaigns);

        foreach ($donations as $donation) {
            $totalCollected += $donation->getAmount();
        }

        // Prepare totals array
        $totals = [
            'totalCollected' => $totalCollected,
            'totalDonations' => $totalDonations,
            'totalDonors' => $totalDonors,
            'totalCampaigns' => $totalCampaigns,
        ];

        // Get recent donations (last 10)
        $recentDonations = array_slice(array_reverse($donations), 0, 10);

        // Calculate top campaigns by total raised
        $campaignStats = [];
        foreach ($campaigns as $campaign) {
            $totalRaised = 0;
            $donationCount = 0;
            foreach ($donations as $donation) {
                if ($donation->getCampaign() === $campaign) {
                    $totalRaised += $donation->getAmount();
                    $donationCount++;
                }
            }
            $campaignStats[] = [
                'campaign' => $campaign,
                'totalRaised' => $totalRaised,
                'donationsCount' => $donationCount,
            ];
        }
        // Sort by total raised descending
        usort($campaignStats, function($a, $b) {
            return $b['totalRaised'] <=> $a['totalRaised'];
        });
        $topCampaigns = array_slice($campaignStats, 0, 5);

        // Calculate top donors by total donated
        $donorStats = [];
        foreach ($donors as $donor) {
            $totalDonated = 0;
            $donationCount = 0;
            $lastDonation = null;
            foreach ($donations as $donation) {
                if ($donation->getDonor() === $donor) {
                    $totalDonated += $donation->getAmount();
                    $donationCount++;
                    if ($lastDonation === null || $donation->getDate() > $lastDonation) {
                        $lastDonation = $donation->getDate();
                    }
                }
            }
            $donorStats[] = [
                'donor' => $donor,
                'totalDonated' => $totalDonated,
                'donationsCount' => $donationCount,
                'lastDonation' => $lastDonation,
            ];
        }
        // Sort by total donated descending
        usort($donorStats, function($a, $b) {
            return $b['totalDonated'] <=> $a['totalDonated'];
        });
        $topDonors = array_slice($donorStats, 0, 5);

        return $this->render('statistics/index.html.twig', [
            'totals' => $totals,
            'recentDonations' => $recentDonations,
            'topCampaigns' => $topCampaigns,
            'topDonors' => $topDonors,
        ]);
    }
}
