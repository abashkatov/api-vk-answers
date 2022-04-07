<?php

namespace App\Controller;

use App\Entity\Question;
use App\Entity\Tag;
use App\Entity\User;
use App\Repository\QuestionRepository;
use App\Repository\TagRepository;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
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

    public function __construct(
        QuestionRepository $questionRepository,
        SerializerInterface $serializer,
        UserRepository $userRepository,
        TagRepository $tagRepository,
        EntityManagerInterface $em
    )
    {
        $this->questionRepository = $questionRepository;
        $this->serializer         = $serializer;
        $this->userRepository = $userRepository;
        $this->tagRepository = $tagRepository;
        $this->em = $em;
    }

    /**
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     */
    #[Route('/group/{groupId<\d+>}/questions/{question<\d+>}', name: 'app_group_questions_get', methods: ['GET'])]
    public function getByGroup(Question $question, int $groupId): Response
    {
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
    #[Route('/group/{groupId<\d+>}/questions/{question<\d+>}', name: 'app_group_questions_get', methods: ['PATCH'])]
    public function patchByGroup(Question $question, int $groupId, Request $request): Response
    {
        if ($question->getGroupId() !== $groupId) {
            throw new NotFoundHttpException();
        }
        $votes = $question->getVoteCount();
        $content = $request->getContent();
        $this->serializer->deserialize($content, Question::class, JsonEncoder::FORMAT, [AbstractNormalizer::OBJECT_TO_POPULATE => $question]);
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
        if ($question->getGroupId() !== null) {
            throw new NotFoundHttpException();
        }
        $votes = $question->getVoteCount();
        $content = $request->getContent();
        $this->serializer->deserialize($content, Question::class, JsonEncoder::FORMAT, [AbstractNormalizer::OBJECT_TO_POPULATE => $question]);
        $question->setVoteCount($votes);
        $question->setUpdatedAt(new \DateTime());
        $this->em->flush();
        $data = $this->serializer->normalize($question);
        return $this->json($data);
    }

    #[Route('/group/{groupId<\d+>}/questions', name: 'app_group_questions_list', methods: ['GET'])]
    public function listByGroup(int $groupId, Request $request): Response
    {
        $page  = (int)$request->query->get('page', 1);
        $limit = (int)$request->query->get('limit', 20);
        $searchString = $request->query->get('search', '');
        $questions = $this->questionRepository->findByNameAndGroup($searchString, $groupId, $page, $limit);
        $data = $this->serializer->normalize($questions);
        return $this->json($data);
    }

    #[Route('/questions', name: 'app_questions_list', methods: ['GET'])]
    public function list(Request $request): Response
    {
        $page  = (int)$request->query->get('page', 1);
        $limit = (int)$request->query->get('limit', 20);
        $searchString = $request->query->get('search', '');
        $questions = $this->questionRepository->findByNameAndGroup($searchString, null, $page, $limit);
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
        $author = $this->userRepository->findOneBy(['vkId' => $question->getAuthor()?->getVkId()]);
        if ($author instanceof User) {
            $question->setAuthor($author);
        }
        $tagNames = $question->getTags()->map(static fn(Tag $tag) => $tag->getTagName())->toArray();
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
}
