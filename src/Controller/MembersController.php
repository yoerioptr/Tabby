<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\MemberRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class MembersController extends AbstractController
{
    public function __construct(private readonly MemberRepository $memberRepository)
    {
        //
    }

    #[Route('/members', name: 'app_members')]
    public function index(): Response
    {

        $members = $this->memberRepository->findAll();

        return $this->render('members.html.twig', [
            'title' => 'Members',
            'members' => $members,
        ]);
    }
}
