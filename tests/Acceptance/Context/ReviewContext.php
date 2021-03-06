<?php

namespace Tests\Acceptance\Context;

use PHPUnit\Framework\Assert;
use Slub\Application\NewReview\NewReview;
use Slub\Application\NewReview\NewReviewHandler;
use Slub\Application\NotifySquad\NotifySquad;
use Slub\Domain\Entity\PR\MessageIdentifier;
use Slub\Domain\Entity\PR\PR;
use Slub\Domain\Entity\PR\PRIdentifier;
use Slub\Domain\Repository\PRNotFoundException;
use Slub\Domain\Repository\PRRepositoryInterface;
use Tests\Acceptance\helpers\ChatClientSpy;
use Tests\Acceptance\helpers\EventsSpy;

class ReviewContext extends FeatureContext
{
    /** @var NewReviewHandler */
    private $reviewHandler;

    /** @var EventsSpy */
    private $eventSpy;

    /** @var ChatClientSpy */
    private $chatClientSpy;

    /** @var PRIdentifier */
    private $currentPRIdentifier;

    /** @var MessageIdentifier */
    private $currentMessageIdentifier;

    public function __construct(
        PRRepositoryInterface $PRRepository,
        NewReviewHandler $reviewHandler,
        EventsSpy $eventSpy,
        ChatClientSpy $chatClientSpy
    ) {
        parent::__construct($PRRepository);

        $this->reviewHandler = $reviewHandler;
        $this->eventSpy = $eventSpy;
        $this->chatClientSpy = $chatClientSpy;
    }

    /**
     * @Given /^a PR in review$/
     */
    public function aPullRequestInReview()
    {
        $this->currentPRIdentifier = PRIdentifier::create('akeneo/pim-community-dev/1010');
        $this->currentMessageIdentifier = MessageIdentifier::fromString('CHANNEL_ID@1');
        $this->PRRepository->save(PR::create($this->currentPRIdentifier, $this->currentMessageIdentifier));
    }

    /**
     * @When /^the PR is GTMed$/
     */
    public function thePullRequestIsGTMed()
    {
        $gtm = new NewReview();
        $gtm->repositoryIdentifier = 'akeneo/pim-community-dev';
        $gtm->PRIdentifier = 'akeneo/pim-community-dev/1010';
        $gtm->reviewStatus = 'accepted';
        $this->reviewHandler->handle($gtm);
    }

    /**
     * @Then /^the PR should be GTMed$/
     */
    public function thePullRequestShouldBeGTMed()
    {
        $PR = $this->PRRepository->getBy($this->currentPRIdentifier);
        Assert::assertEquals(1, $PR->normalize()['GTMS']);
    }

    /**
     * @Then /^the squad should be notified that the PR has one more GTM$/
     */
    public function theSquadShouldBeNotifiedThatThePullRequestHasOneMoreGTM()
    {
        Assert::assertNotNull($this->currentPRIdentifier, 'The PR identifier was not created');
        $PR = $this->PRRepository->getBy($this->currentPRIdentifier);
        $GTMCount = $PR->normalize()['GTMS'];
        Assert::assertEquals(1, $GTMCount, sprintf('The PR has %d GTMS, expected %d', $GTMCount, 1));
        Assert::assertTrue($this->eventSpy->PRGMTedDispatched());
        $this->chatClientSpy->assertHasBeenCalledWith(
            $this->currentMessageIdentifier,
            NotifySquad::MESSAGE_PR_GTMED
        );
    }

    /**
     * @When /^the PR is NOT GTMED$/
     */
    public function thePullRequestIsNOTGTMED()
    {
        $notGTM = new NewReview();
        $notGTM->repositoryIdentifier = 'akeneo/pim-community-dev';
        $notGTM->PRIdentifier = 'akeneo/pim-community-dev/1010';
        $notGTM->reviewStatus = 'refused';
        $this->reviewHandler->handle($notGTM);
    }

    /**
     * @Then /^the PR should be NOT GTMed$/
     */
    public function thePullRequestShouldBeNOTGTMed()
    {
        $PR = $this->PRRepository->getBy($this->currentPRIdentifier);
        Assert::assertEquals(1, $PR->normalize()['NOT_GTMS']);
    }

    /**
     * @Then /^the squad should be notified that the PR has one more NOT GTM$/
     */
    public function theSquadShouldBeNotifiedThatThePullRequestHasOneMoreNOTGTM()
    {
        Assert::assertNotNull($this->currentPRIdentifier, 'The PR identifier was not created');
        $PR = $this->PRRepository->getBy($this->currentPRIdentifier);
        $notGTMCount = $PR->normalize()['NOT_GTMS'];
        Assert::assertEquals(1, $notGTMCount, sprintf('The PR has %d NOT GTMS, expected %d', $notGTMCount, 1));
        Assert::assertTrue($this->eventSpy->PRNotGMTedDispatched());
        $this->chatClientSpy->assertHasBeenCalledWith(
            $this->currentMessageIdentifier,
            NotifySquad::MESSAGE_PR_NOT_GTMED
        );
    }

    /**
     * @When /^a PR is reviewed on an unsupported repository$/
     */
    public function aPullRequestIsReviewedOnAnUnsupportedRepository()
    {
        $this->currentPRIdentifier = PRIdentifier::fromString('1010');

        $notGTM = new NewReview();
        $notGTM->repositoryIdentifier = 'unsupported_repository';
        $notGTM->PRIdentifier = '1010';
        $notGTM->reviewStatus = 'approved';

        $this->reviewHandler->handle($notGTM);
    }

    /**
     * @Then /^it does not notify the squad$/
     */
    public function itDoesNotNotifyTheSquad()
    {
        Assert::assertNotNull($this->currentPRIdentifier, 'The PR identifier was not created');
        Assert::assertFalse($this->PRExists($this->currentPRIdentifier), 'PR should not exist but was found.');
        Assert::assertFalse($this->eventSpy->PRNotGMTedDispatched(), 'Event has been thrown, while none was expected.');
    }

    private function PRExists(PRIdentifier $PRIdentifier): bool
    {
        $found = true;
        try {
            $this->PRRepository->getBy($PRIdentifier);
        } catch (PRNotFoundException $notFoundException) {
            $found = false;
        }

        return $found;
    }

    /**
     * @When /^the PR is commented$/
     */
    public function thePRIsCommented()
    {
        $comment = new NewReview();
        $comment->repositoryIdentifier = 'akeneo/pim-community-dev';
        $comment->PRIdentifier = 'akeneo/pim-community-dev/1010';
        $comment->reviewStatus = 'commented';
        $this->reviewHandler->handle($comment);
    }

    /**
     * @Then /^the PR should have one comment$/
     */
    public function thePRShouldHaveOneComment()
    {
        $PR = $this->PRRepository->getBy($this->currentPRIdentifier);
        Assert::assertEquals(1, $PR->normalize()['COMMENTS']);
    }

    /**
     * @Given /^the squad should be notified that the PR has one more comment$/
     */
    public function theSquadShouldBeNotifiedThatThePRHasOneMoreComment()
    {
        Assert::assertNotNull($this->currentPRIdentifier, 'The PR identifier was not commented');
        $PR = $this->PRRepository->getBy($this->currentPRIdentifier);
        $notGTMCount = $PR->normalize()['COMMENTS'];
        Assert::assertEquals(1, $notGTMCount, sprintf('The PR has %d COMMENTS, expected %d', $notGTMCount, 1));
        Assert::assertTrue($this->eventSpy->PRCommentedDispatched());
        $this->chatClientSpy->assertHasBeenCalledWith(
            $this->currentMessageIdentifier,
            NotifySquad::MESSAGE_PR_COMMENTED
        );
    }
}
