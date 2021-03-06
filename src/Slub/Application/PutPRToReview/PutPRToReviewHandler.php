<?php

declare(strict_types=1);

namespace Slub\Application\PutPRToReview;

use Psr\Log\LoggerInterface;
use Slub\Domain\Entity\Channel\ChannelIdentifier;
use Slub\Domain\Entity\PR\MessageIdentifier;
use Slub\Domain\Entity\PR\PR;
use Slub\Domain\Entity\PR\PRIdentifier;
use Slub\Domain\Entity\Repository\RepositoryIdentifier;
use Slub\Domain\Query\IsSupportedInterface;
use Slub\Domain\Repository\PRNotFoundException;
use Slub\Domain\Repository\PRRepositoryInterface;

class PutPRToReviewHandler
{
    /** @var PRRepositoryInterface */
    private $PRRepository;

    /** @var IsSupportedInterface */
    private $isSupported;

    /** @var LoggerInterface */
    private $logger;

    public function __construct(
        PRRepositoryInterface $PRRepository,
        IsSupportedInterface $isRepositorySupported,
        LoggerInterface $logger
    ) {
        $this->PRRepository = $PRRepository;
        $this->isSupported = $isRepositorySupported;
        $this->logger = $logger;
    }

    public function handle(PutPRToReview $command)
    {
        if ($this->isUnsupported($command)) {
            return;
        }

        if ($this->PRExists($command)) {
            $this->attachMessageToPR($command);
        } else {
            $this->createNewPR($command);
        }

        $this->logger->info(sprintf('PR "%s" has been put to review', $command->PRIdentifier));
    }

    private function isUnsupported(PutPRToReview $putPRToReview): bool
    {
        $repositoryIdentifier = RepositoryIdentifier::fromString($putPRToReview->repositoryIdentifier);
        $channelIdentifier = ChannelIdentifier::fromString($putPRToReview->channelIdentifier);

        $isUnsupported = !$this->isSupported->repository($repositoryIdentifier)
            || !$this->isSupported->channel($channelIdentifier);

        if ($isUnsupported) {
            $this->logger->info(
                sprintf(
                    'PR "%s" was not put to review because it is not supported for channel "%s", and repository "%s"',
                    $putPRToReview->PRIdentifier,
                    $putPRToReview->repositoryIdentifier,
                    $putPRToReview->channelIdentifier
                )
            );
        }

        return $isUnsupported;
    }

    private function PRExists(PutPRToReview $putPRToReview): bool
    {
        try {
            $this->PRRepository->getBy(PRIdentifier::fromString($putPRToReview->PRIdentifier));

            return true;
        } catch (PRNotFoundException $exception) {
            return false;
        }
    }

    private function attachMessageToPR(PutPRToReview $putPRToReview): void
    {
        $PR = $this->PRRepository->getBy(PRIdentifier::fromString($putPRToReview->PRIdentifier));
        $PR->putToReviewAgainViaMessage(MessageIdentifier::create($putPRToReview->messageIdentifier));
        $this->PRRepository->save($PR);
    }

    private function createNewPR(PutPRToReview $putPRToReview): void
    {
        $this->PRRepository->save(
            PR::create(
                PRIdentifier::create($putPRToReview->PRIdentifier),
                MessageIdentifier::fromString($putPRToReview->messageIdentifier)
            )
        );
    }
}
