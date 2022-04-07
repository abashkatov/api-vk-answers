<?php

namespace App\Controller;

use App\Entity\Question;
use App\Entity\Tag;
use App\Entity\User;
use App\Repository\QuestionRepository;
use App\Repository\TagRepository;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;

class QuestionController extends AbstractController
{
    private QuestionRepository $questionRepository;
    private Serializer         $serializer;
    private UserRepository     $userRepository;
    private TagRepository      $tagRepository;

    public function __construct(
        QuestionRepository $questionRepository,
        SerializerInterface $serializer,
        UserRepository $userRepository,
        TagRepository $tagRepository
    )
    {
        $this->questionRepository = $questionRepository;
        $this->serializer         = $serializer;
        $this->userRepository = $userRepository;
        $this->tagRepository = $tagRepository;
    }

    #[Route('/group/{groupId}/questions', name: 'app_group_questions_list', requirements: ['groupId' => '\d+'], methods: ['GET'])]
    public function listByGroup(int $groupId, Request $request): Response
    {
        $searchString = $request->query->get('search', '');
        $questions = $this->questionRepository->findByNameAndGroup($searchString, $groupId);
        $data = $this->serializer->normalize($questions);
        return $this->json($data);
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
