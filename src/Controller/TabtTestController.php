<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Yoerioptr\TabtApiClient\TabtInterface;

final class TabtTestController extends AbstractController
{
    public function __construct(private readonly TabtInterface $tabt)
    {
        //
    }

    #[Route('/tabt/test', name: 'app_tabt_test')]
    public function index(): Response
    {
        dump($this->tabt->test()->info());

        return $this->render('tabt_test/index.html.twig', [
            'controller_name' => 'TabtTestController',
        ]);
    }
}
