<?php

namespace App\Controller;

use App\Repository\QuestionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CommunityController extends AbstractController
{
    private QuestionRepository $questionRepository;

    public function __construct(QuestionRepository $questionRepository)
    {
        $this->questionRepository = $questionRepository;
    }

    /**
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     */
    #[Route('/communities', name: 'app_communities_list', methods: ['GET'])]
    public function list(Request $request): Response
    {
        $userVkId = (int)$request->headers->get('X-VK-ID');
        $communities = $this->questionRepository->findCommunities($userVkId);

        return $this->json($communities);
    }
}
