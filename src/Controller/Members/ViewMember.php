<?php

declare(strict_types=1);

namespace App\Controller\Members;

use App\Repository\MemberRepository;
use App\Service\MemberContactProvider;
use App\Service\MemberPerformanceProvider;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ViewMember extends AbstractController
{
    public function __construct(
        private readonly MemberRepository $memberRepository,
        private readonly MemberContactProvider $contactProvider,
        private readonly MemberPerformanceProvider $performanceProvider,
    ) {
        //
    }

    #[Route('/members/{id}', name: 'app_member', requirements: ['id' => '\d+'])]
    public function __invoke(int $id): Response
    {
        $member = $this->memberRepository->find($id);

        if (null === $member) {
            throw $this->createNotFoundException(sprintf('Member "%d" was not found.', $id));
        }

        return $this->render('member.html.twig', [
            'title' => sprintf('%s %s', $member->getFirstName(), $member->getLastName()),
            'member' => $member,
            'contacts' => $this->contactProvider->forMember($member),
            'performance' => $this->performanceProvider->forMember($member),
        ]);
    }
}
