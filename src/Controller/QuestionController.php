<?php

namespace App\Controller;

use App\Entity\Question;
use App\Entity\QuestionVote;
use App\Entity\Tag;
use App\Entity\User;
use App\Repository\QuestionRepository;
use App\Repository\QuestionVoteRepository;
use App\Repository\TagRepository;
use App\Repository\UserRepository;
use App\Service\QuestionVoteService;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;

class QuestionController extends AbstractController
{
    private QuestionRepository     $questionRepository;
    private Serializer             $serializer;
    private UserRepository         $userRepository;
    private TagRepository          $tagRepository;
    private EntityManagerInterface $em;
    private LoggerInterface        $logger;
    private QuestionVoteRepository $questionVoteRepository;
    private QuestionVoteService    $questionVoteService;

    public function __construct(
        QuestionRepository $questionRepository,
        SerializerInterface $serializer,
        UserRepository $userRepository,
        TagRepository $tagRepository,
        EntityManagerInterface $em,
        LoggerInterface $logger,
        QuestionVoteRepository $questionVoteRepository,
        QuestionVoteService $questionVoteService,
    ) {
        $this->questionRepository     = $questionRepository;
        $this->serializer             = $serializer;
        $this->userRepository         = $userRepository;
        $this->tagRepository          = $tagRepository;
        $this->em                     = $em;
        $this->logger                 = $logger;
        $this->questionVoteRepository = $questionVoteRepository;
        $this->questionVoteService    = $questionVoteService;
    }

    /**
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     */
    #[Route('/group/{groupId<\d+>}/questions/{question<\d+>}', name: 'app_group_questions_get', methods: ['GET'])]
    public function getByGroup(Question $question, int $groupId): Response
    {
        $this->logger->debug(__METHOD__);
        if ($question->getGroupId() !== $groupId) {
            throw new NotFoundHttpException();
        }
        $data = $this->serializer->normalize($question);

        return $this->json($data);
    }

    /**
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     */
    #[Route('/questions/{question<\d+>}', name: 'app_questions_get', methods: ['GET'])]
    public function getQuestion(Question $question): Response
    {
        $this->logger->debug(__METHOD__);
        if ($question->getGroupId() !== null) {
            throw new NotFoundHttpException();
        }
        $data = $this->serializer->normalize($question);

        return $this->json($data);
    }

    /**
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     * @throws \JsonException
     */
    #[Route('/group/{groupId<\d+>}/questions/{question<\d+>}', name: 'app_group_questions_patch', methods: ['PATCH'])]
    public function patchByGroup(Question $question, int $groupId, Request $request): Response
    {
        $this->logger->debug(__METHOD__);
        if ($question->getGroupId() !== $groupId) {
            throw new NotFoundHttpException();
        }
        $votes   = $question->getVoteCount();
        $content = $request->getContent();
        $this->serializer->deserialize(
            $content,
            Question::class,
            JsonEncoder::FORMAT,
            [AbstractNormalizer::OBJECT_TO_POPULATE => $question]
        );
        $userVkId = (int)$request->headers->get('X-VK-ID');
        if ($question->getAuthor() === null || $question->getAuthor()->getId() !== $userVkId) {
            throw new UnauthorizedHttpException('Permissions denied');
        }
        $question->setVoteCount($votes);
        $question->setUpdatedAt(new \DateTime());
        $this->em->flush();
        $data = $this->serializer->normalize($question);

        return $this->json($data);
    }

    /**
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     * @throws \JsonException
     */
    #[Route('/questions/{question<\d+>}', name: 'app_questions_patch', methods: ['PATCH'])]
    public function patchQuestion(Question $question, Request $request): Response
    {
        $this->logger->debug(__METHOD__);
        if ($question->getGroupId() !== null) {
            throw new NotFoundHttpException();
        }
        $votes   = $question->getVoteCount();
        $content = $request->getContent();
        $this->serializer->deserialize(
            $content,
            Question::class,
            JsonEncoder::FORMAT,
            [AbstractNormalizer::OBJECT_TO_POPULATE => $question]
        );
        $userVkId = (int)$request->headers->get('X-VK-ID');
        if ($question->getAuthor() === null || $question->getAuthor()->getId() !== $userVkId) {
            throw new UnauthorizedHttpException('Permissions denied');
        }
        $question->setVoteCount($votes);
        $question->setUpdatedAt(new \DateTime());
        $this->em->flush();
        $data = $this->serializer->normalize($question);

        return $this->json($data);
    }

    /**
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     */
    #[Route('/group/{groupId<\d+>}/questions', name: 'app_group_questions_list', methods: ['GET'])]
    public function listByGroup(int $groupId, Request $request): Response
    {
        $this->logger->debug(__METHOD__);
        $page         = (int)$request->query->get('page', 1);
        $limit        = (int)$request->query->get('limit', 20);
        $searchString = (string)$request->query->get('search', '');
        $tagString    = (string)$request->query->get('tags', '');
        $tagNames     = explode(',', $tagString);
        $questions    = $this->questionRepository->findByNameAndGroup(
            $searchString,
            $groupId,
            $tagNames,
            $page,
            $limit
        );
        $data         = $this->serializer->normalize($questions);

        return $this->json($data);
    }

    /**
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     */
    #[Route('/questions', name: 'app_questions_list', methods: ['GET'])]
    public function list(Request $request): Response
    {
        $this->logger->debug(__METHOD__);
        $page         = (int)$request->query->get('page', 1);
        $limit        = (int)$request->query->get('limit', 20);
        $searchString = (string)$request->query->get('search', '');
        $tagString    = (string)$request->query->get('tags', '');
        $tagNames     = explode(',', $tagString);
        $questions    = $this->questionRepository->findByNameAndGroup($searchString, null, $tagNames, $page, $limit);
        $data         = $this->serializer->normalize($questions);

        return $this->json($data);
    }

    /**
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     */
    #[Route('/questions/my', name: 'app_questions_my_list', methods: ['GET'])]
    public function listMy(Request $request): Response
    {
        $this->logger->debug(__METHOD__);
        $userVkId     = (int)$request->headers->get('X-VK-ID');
        $page         = (int)$request->query->get('page', 1);
        $limit        = (int)$request->query->get('limit', 20);
        $searchString = (string)$request->query->get('search', '');
        $tagString    = (string)$request->query->get('tags', '');
        $tagNames     = explode(',', $tagString);
        $questions    = $this->questionRepository->findByNameAndUserVkId(
            $searchString,
            $userVkId,
            $tagNames,
            $page,
            $limit
        );
        $data         = $this->serializer->normalize($questions);

        return $this->json($data);
    }

    /**
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     */
    #[Route('/group/{groupId<\d+>}/questions/my', name: 'app_group_questions_my_list', methods: ['GET'])]
    public function listGroupMy(int $groupId, Request $request): Response
    {
        $userVkId     = (int)$request->headers->get('X-VK-ID');
        $page         = (int)$request->query->get('page', 1);
        $limit        = (int)$request->query->get('limit', 20);
        $searchString = (string)$request->query->get('search', '');
        $tagString    = (string)$request->query->get('tags', '');
        $tagNames     = explode(',', $tagString);
        $questions    = $this->questionRepository->findByNameAndUserVkIdAndGroupId(
            $searchString,
            $userVkId,
            $groupId,
            $tagNames,
            $page,
            $limit
        );
        $data         = $this->serializer->normalize($questions);

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
        $this->logger->debug(__METHOD__);
        $data = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);
        /** @var Question $question */
        $question = $this->serializer->denormalize($data, Question::class);
        $userVkId = (int)$request->headers->get('X-VK-ID');
        if ($question->getAuthor() === null || $question->getAuthor()->getId() !== $userVkId) {
            throw new UnauthorizedHttpException('Permissions denied');
        }
        $author = $this->userRepository->findOneBy(['vkId' => $question->getAuthor()?->getVkId()]);
        if ($author instanceof User) {
            $question->setAuthor($author);
        }
        $tagNames     = $question->getTags()->map(static fn(Tag $tag) => $tag->getTagName())->toArray();
        $existingTags = [];
        foreach ($this->tagRepository->findAllByTagNames($tagNames) as $tag) {
            $existingTags[$tag->getTagName()] = $tag;
        }
        $tags = \array_map(
            static fn(Tag $tag) => $existingTags[$tag->getTagName()] ?? $tag,
            $question->getTags()->toArray()
        );
        $question->setTags(new ArrayCollection($tags));
        $question
            ->setCreatedAt(new \DateTimeImmutable())
            ->setUpdatedAt(new \DateTime());
        $this->questionRepository->add($question);

        return $this->json($question);
    }

    /**
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     */
    #[Route('/group/{groupId<\d+>}/questions/{question<\d+>}/voteUp', name: 'app_group_questions_vote_up', methods: ['PUT'])]
    public function voteGroupUp(Request $request, int $groupId, Question $question): Response
    {
        if ($question->getGroupId() !== $groupId) {
            throw new NotFoundHttpException();
        }
        $userVkId = (int)$request->headers->get('X-VK-ID');
        $this->questionVoteService->vote($userVkId, $question, 1);
        $this->em->flush();
        $totalVote = $this->questionVoteRepository->sum($userVkId, $question);
        $question->setVoteCount($totalVote);
        $this->em->flush();
        $data = $this->serializer->normalize($question);

        return $this->json($data);
    }

    /**
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     * @throws \Doctrine\ORM\ORMException
     */
    #[Route('/group/{groupId<\d+>}/questions/{question<\d+>}/voteDown', name: 'app_group_questions_vote_down', methods: ['PUT'])]
    public function voteGroupDown(Request $request, int $groupId, Question $question): Response
    {
        if ($question->getGroupId() !== $groupId) {
            throw new NotFoundHttpException();
        }
        $userVkId = (int)$request->headers->get('X-VK-ID');
        $this->questionVoteService->vote($userVkId, $question, -1);
        $this->em->flush();
        $totalVote = $this->questionVoteRepository->sum($userVkId, $question);
        $question->setVoteCount($totalVote);
        $this->em->flush();
        $data = $this->serializer->normalize($question);

        return $this->json($data);
    }

    /**
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     */
    #[Route('/questions/{question<\d+>}/voteUp', name: 'app_questions_vote_up', methods: ['PUT'])]
    public function voteUp(Request $request, Question $question): Response
    {
        if ($question->getGroupId() !== null) {
            throw new NotFoundHttpException();
        }
        $userVkId = (int)$request->headers->get('X-VK-ID');
        $this->questionVoteService->vote($userVkId, $question, 1);
        $this->em->flush();
        $totalVote = $this->questionVoteRepository->sum($userVkId, $question);
        $question->setVoteCount($totalVote);
        $this->em->flush();
        $data = $this->serializer->normalize($question);

        return $this->json($data);
    }

    /**
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     * @throws \Doctrine\ORM\ORMException
     */
    #[Route('/questions/{question<\d+>}/voteDown', name: 'app_questions_vote_down', methods: ['PUT'])]
    public function voteDown(Request $request, Question $question): Response
    {
        if ($question->getGroupId() !== null) {
            throw new NotFoundHttpException();
        }
        $userVkId = (int)$request->headers->get('X-VK-ID');
        $this->questionVoteService->vote($userVkId, $question, -1);
        $this->em->flush();
        $totalVote = $this->questionVoteRepository->sum($userVkId, $question);
        $question->setVoteCount($totalVote);
        $this->em->flush();
        $data = $this->serializer->normalize($question);

        return $this->json($data);
    }
}
