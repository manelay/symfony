<?php
// src/Controller/HomeController.php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;        // Pour retourner une réponse
use Symfony\Component\Routing\Annotation\Route;        // Pour les annotations de route

class HomeController extends AbstractController
{
    #[Route('/home', name: 'app_home')]
    public function index(): Response
    {
        return new Response("Bonjour mes étudiants");
    }
}
