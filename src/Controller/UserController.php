<?php

namespace App\Controller;

use App\Entity\Answer;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;

class UserController extends AbstractController
{
    private UserRepository $userRepository;
    private Serializer $serializer;
    private EntityManagerInterface $em;

    public function __construct(
        UserRepository $userRepository,
        SerializerInterface $serializer,
        EntityManagerInterface $em
    ) {
        $this->userRepository = $userRepository;
        $this->serializer     = $serializer;
        $this->em             = $em;
    }

    #[Route('/user/{userVkId<\d+>}', name: 'app_user_patch', methods: ['PATCH'])]
    public function index(Request $request, int $userVkId): Response
    {
        $user = $this->userRepository->findOneBy(['vkId' => $userVkId]);
        if (null === $user) {
            $user = new User();
            $this->userRepository->add($user, false);
        }
        $this->serializer->deserialize(
            $request->getContent(),
            User::class,
            JsonEncoder::FORMAT,
            [AbstractNormalizer::OBJECT_TO_POPULATE => $user]
        );
        $this->em->flush();

        return $this->json($user);
    }
}
