<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Question;
use App\Entity\QuestionVote;
use App\Entity\User;
use App\Repository\QuestionVoteRepository;
use App\Repository\UserRepository;

class QuestionVoteService
{
    private QuestionVoteRepository $questionVoteRepository;
    private UserRepository         $userRepository;

    public function __construct(
        QuestionVoteRepository $questionVoteRepository,
        UserRepository $userRepository,
    )
    {
        $this->questionVoteRepository = $questionVoteRepository;
        $this->userRepository = $userRepository;
    }

    /**
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\ORMException
     */
    public function vote(int $userVkId, Question $question, int $voteValue): void
    {
        $author = $this->userRepository->findOneBy(['vkId' => $userVkId]);
        $vote = $this->questionVoteRepository->findOneBy([
            'author' => $author,
            'question' => $question,
        ]);
        if (null === $vote) {
            $author = $author ?? (new User())->setVkId($userVkId);
            $vote = (new QuestionVote())
                ->setQuestion($question)
                ->setAuthor($author);
            $this->questionVoteRepository->add($vote, false);
        }
        $vote->setValue($voteValue);
    }
}
