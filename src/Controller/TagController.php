<?php

namespace App\Controller;

use App\Repository\TagRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TagController extends AbstractController
{
    private TagRepository $tagRepository;

    public function __construct(TagRepository $tagRepository)
    {
        $this->tagRepository = $tagRepository;
    }

    #[Route('/tags', name: 'app_tags')]
    public function list(Request $request): Response
    {
        $searchString = $request->query->get('search', '');
        $tags = $this->tagRepository->findByTagName($searchString);
        return $this->json($tags);
    }
}
