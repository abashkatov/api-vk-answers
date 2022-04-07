<?php

namespace App\Controller;

use App\Entity\Answer;
use App\Entity\Question;
use App\Entity\User;
use App\Repository\AnswerRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use http\Exception\InvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;

class AnswerController extends AbstractController
{
    private Serializer             $serializer;
    private UserRepository         $userRepository;
    private AnswerRepository       $answerRepository;
    private EntityManagerInterface $em;

    public function __construct(
        SerializerInterface $serializer,
        UserRepository $userRepository,
        AnswerRepository $answerRepository,
        EntityManagerInterface $em,
    ) {
        $this->serializer       = $serializer;
        $this->userRepository   = $userRepository;
        $this->answerRepository = $answerRepository;
        $this->em = $em;
    }

    /**
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     */
    #[Route('/questions/{question<\d+>}/answers', name: 'app_questions_answers_list', methods: ['GET'])]
    public function list(Request $request): Response
    {
        $page  = (int)$request->query->get('page', 1);
        $limit = (int)$request->query->get('limit', 20);
        if ($page < 1 || $limit < 1) {
            throw new InvalidArgumentException();
        }
        $answers = $this->answerRepository->findBy([], null, $limit, ($page - 1) * $limit);
        $data    = $this->serializer->normalize($answers);

        return $this->json($data);
    }

    /**
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     */
    #[Route('/answers/my', name: 'app_answers_my', methods: ['GET'])]
    public function listMy(Request $request): Response
    {
        $userVkId     = (int)$request->headers->get('X-VK-ID');
        $page         = (int)$request->query->get('page', 1);
        $limit        = (int)$request->query->get('limit', 20);
        $searchString = $request->query->get('search', '');
        $questions    = $this->answerRepository->findByNameAndUserVkId($searchString, $userVkId, $page, $limit);
        $data         = $this->serializer->normalize($questions);

        return $this->json($data);
    }

    /**
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\ORMException
     */
    #[Route('/questions/{question<\d+>}/answers/{answer<\d+>}', name: 'app_questions_answer_patch', methods: ['PATCH'])]
    public function patch(Question $question, Answer $answer, Request $request): Response
    {
        if ($answer->getQuestion() === null || $answer->getQuestion()->getId() !== $question->getId()) {
            throw new NotFoundHttpException();
        }
        $this->serializer->deserialize($request->getContent(), Answer::class, JsonEncoder::FORMAT, [AbstractNormalizer::OBJECT_TO_POPULATE => $answer]);
        $answer->setUpdatedAt(new \DateTime());
        $this->em->flush();
        $data = $this->serializer->normalize($answer);

        return $this->json($data);
    }

    /**
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\ORMException
     */
    #[Route('/questions/{question<\d+>}/answers', name: 'app_questions_answer_post', methods: ['POST'])]
    public function post(Question $question, Request $request): Response
    {
        /** @var Answer $answer */
        $answer = $this->serializer->deserialize($request->getContent(), Answer::class, JsonEncoder::FORMAT);
        $author = $this->userRepository->findOneBy(['vkId' => $answer->getAuthor()?->getVkId()]);
        if ($author instanceof User) {
            $answer->setAuthor($author);
        }
        $answer->setQuestion($question);
        $answer
            ->setCreatedAt(new \DateTimeImmutable())
            ->setUpdatedAt(new \DateTime());
        $this->answerRepository->add($answer);
        $data = $this->serializer->normalize($answer);

        return $this->json($data);
    }
}
