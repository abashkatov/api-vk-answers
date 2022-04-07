<?php

namespace App\Controller;

use App\Entity\Question;
use App\Repository\QuestionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;

class QuestionController extends AbstractController
{
    private QuestionRepository  $questionRepository;
    private Serializer $serializer;

    public function __construct(QuestionRepository $questionRepository, SerializerInterface $serializer)
    {
        $this->questionRepository = $questionRepository;
        $this->serializer         = $serializer;
    }

    #[Route('/questions', name: 'app_questions_list', methods: ['GET'])]
    public function list(Request $request): Response
    {
        $searchString = $request->query->get('search', '');
        $questions = $this->questionRepository->findByNameAndGroup($searchString);
        $data = $this->serializer->normalize($questions);
        return $this->json($data);
    }

    /**
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     * @throws \JsonException
     */
    #[Route('/questions', name: 'app_questions_post', methods: ['POST'])]
    public function post(Request $request): Response
    {
        $data = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);
        /** @var Question $question */
        $question = $this->serializer->denormalize($data, Question::class);
        $question
            ->setCreatedAt(new \DateTimeImmutable())
            ->setUpdatedAt(new \DateTime());
        $this->questionRepository->add($question);

        return $this->json($question);
    }
}
