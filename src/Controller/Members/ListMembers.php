<?php

declare(strict_types=1);

namespace App\Controller\Members;

use App\Dto\MemberFilter;
use App\Dto\MemberSortQuery;
use App\Form\MemberFilterType;
use App\Repository\MemberRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;

final class ListMembers extends AbstractController
{
    public function __construct(
        private readonly MemberRepository $memberRepository,
    ) {
        //
    }

    #[Route('/members', name: 'app_members')]
    public function __invoke(
        Request $request,
        #[MapQueryString(validationFailedStatusCode: Response::HTTP_BAD_REQUEST)]
        MemberSortQuery $sortQuery = new MemberSortQuery(),
    ): Response {
        $filter = new MemberFilter();
        $form = $this->createForm(MemberFilterType::class, $filter, [
            'rankings' => $this->memberRepository->distinctRankings(),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $filter = $form->getData();
        }

        $sort = $sortQuery->toSort();

        return $this->render('members.html.twig', [
            'title' => 'Members',
            'members' => $this->memberRepository->findByFilter($filter, $sort),
            'filterForm' => $form->createView(),
            'sort' => $sort,
        ]);
    }
}
