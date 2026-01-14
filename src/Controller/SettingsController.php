<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
class SettingsController extends AbstractController
{
    #[Route('/settings', name: 'app_settings')]
    public function index(Request $request): Response
    {
        // Handle dark mode toggle
        if ($request->isMethod('POST')) {
            $darkMode = $request->request->get('dark_mode') === 'on';
            // In a real app, you'd save this to user preferences in database
            // For now, we'll just handle it with session
            $request->getSession()->set('dark_mode', $darkMode);

            $this->addFlash('success', 'Settings updated successfully!');
            return $this->redirectToRoute('app_settings');
        }

        $darkMode = $request->getSession()->get('dark_mode', false);

        return $this->render('settings/index.html.twig', [
            'dark_mode' => $darkMode,
        ]);
    }
}