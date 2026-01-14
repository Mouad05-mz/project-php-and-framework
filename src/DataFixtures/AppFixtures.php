<?php

namespace App\DataFixtures;

use App\Entity\Campaign;
use App\Entity\Donation;
use App\Entity\Donor;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();

        // Create donors
        $donors = [];
        for ($i = 0; $i < 10; $i++) {
            $donor = new Donor();
            $donor->setFirstName($faker->firstName);
            $donor->setLastName($faker->lastName);
            $donor->setEmail($faker->email);
            $donor->setPhone($faker->phoneNumber);
            $donor->setAddress($faker->address);

            $manager->persist($donor);
            $donors[] = $donor;
        }

        // Create campaigns
        $campaigns = [];
        for ($i = 0; $i < 3; $i++) {
            $campaign = new Campaign();
            $campaign->setTitle($faker->sentence(3));
            $campaign->setDescription($faker->paragraph);
            $campaign->setGoalAmount($faker->randomFloat(2, 1000, 10000));
            $campaign->setCurrentAmount(0.0);
            $campaign->setStartDate($faker->dateTimeBetween('-1 month'));
            $campaign->setEndDate($faker->dateTimeBetween('now', '+1 month'));
            $campaign->setStatus('active');

            $manager->persist($campaign);
            $campaigns[] = $campaign;
        }

        // Create donations
        foreach ($campaigns as $campaign) {
            for ($i = 0; $i < rand(5, 15); $i++) {
                $donation = new Donation();
                $donation->setAmount($faker->randomFloat(2, 10, 500));
                $donation->setDate($faker->dateTimeBetween($campaign->getStartDate(), 'now'));
                $donation->setMessage($faker->sentence);
                $donation->setDonor($faker->randomElement($donors));
                $donation->setCampaign($campaign);

                $manager->persist($donation);

                // Update campaign amount
                $campaign->setCurrentAmount($campaign->getCurrentAmount() + $donation->getAmount());
            }
            $manager->persist($campaign);
        }

        $manager->flush();
    }
}
