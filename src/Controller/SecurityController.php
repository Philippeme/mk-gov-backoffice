<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SecurityController extends AbstractController
{
    #[Route('/admin/login', name: 'admin_login')]
    public function login(Request $request): Response
    {
        // Si l'utilisateur est déjà connecté, rediriger vers le dashboard
        if ($this->getUser()) {
            return $this->redirectToRoute('admin_dashboard');
        }

        $error = null;
        $lastUsername = '';

        if ($request->isMethod('POST')) {
            $username = $request->request->get('_username');
            $password = $request->request->get('_password');
            
            // Simulation de l'authentification
            if ($this->authenticateUser($username, $password)) {
                // Simulation de la session utilisateur
                $request->getSession()->set('authenticated_user', [
                    'id' => 1,
                    'username' => $username,
                    'email' => $username . '@mkgov.cm',
                    'role' => 'admin',
                    'name' => 'Administrator'
                ]);

                $this->addFlash('success', 'Welcome back!');
                return $this->redirectToRoute('admin_dashboard');
            } else {
                $error = 'Invalid credentials';
                $lastUsername = $username;
            }
        }

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
            'page_title' => 'MK Gov - Admin Login'
        ]);
    }

    #[Route('/admin/logout', name: 'admin_logout')]
    public function logout(): Response
    {
        // Cette méthode peut être vide - elle sera interceptée par le système de sécurité
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    private function authenticateUser(string $username, string $password): bool
    {
        // Simulation simple d'authentification
        // En production, ceci devrait utiliser un système d'authentification robuste
        $validUsers = [
            'admin' => 'admin123',
            'officer' => 'officer123',
            'manager' => 'manager123'
        ];

        return isset($validUsers[$username]) && $validUsers[$username] === $password;
    }

    private function getUser(): ?array
    {
        // Simulation de récupération de l'utilisateur actuel
        // En production, ceci utiliserait le système de sécurité de Symfony
        $session = $this->container->get('request_stack')->getCurrentRequest()->getSession();
        return $session->get('authenticated_user');
    }
}