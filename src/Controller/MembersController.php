<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class MembersController extends AbstractController
{
    #[Route('/members', name: 'app_members')]
    public function index(): Response
    {
        return $this->render('placeholder.html.twig', [
            'title' => 'Members',
        ]);
    }
}
