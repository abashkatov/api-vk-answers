<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Answer;
use App\Entity\AnswerVote;
use App\Entity\User;
use App\Repository\AnswerVoteRepository;
use App\Repository\UserRepository;

class AnswerVoteService
{
    private AnswerVoteRepository $answerVoteRepository;
    private UserRepository       $userRepository;

    public function __construct(
        AnswerVoteRepository $answerVoteRepository,
        UserRepository $userRepository,
    ) {
        $this->userRepository       = $userRepository;
        $this->answerVoteRepository = $answerVoteRepository;
    }

    /**
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\ORMException
     */
    public function vote(int $userVkId, Answer $answer, int $voteValue): void
    {
        $author = $this->userRepository->findOneBy(['vkId' => $userVkId]);
        $vote   = $this->answerVoteRepository->findOneBy([
            'author' => $author,
            'answer' => $answer,
        ]);
        if (null === $vote) {
            $author = $author ?? (new User())->setVkId($userVkId);
            $vote   = (new AnswerVote())
                ->setAnswer($answer)
                ->setAuthor($author);
            $this->answerVoteRepository->add($vote, false);
        }
        $vote->setValue($voteValue);
    }

}
