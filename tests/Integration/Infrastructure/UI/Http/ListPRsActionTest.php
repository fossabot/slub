<?php

declare(strict_types=1);

namespace Tests\Integration\Infrastructure\UI\Http;

use Slub\Domain\Entity\PR\PR;
use Slub\Domain\Repository\PRRepositoryInterface;
use Tests\Integration\Infrastructure\KernelTestCase;
use Tests\Integration\Infrastructure\WebTestCase;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 */
class ListPRsActionTest extends WebTestCase
{
    /** @var PRRepositoryInterface */
    private $PRRepository;

    public function setUp(): void
    {
        parent::setUp();

        $this->PRRepository = $this->get('slub.infrastructure.persistence.pr_repository');
        $this->PRRepository->reset();
    }

    /**
     * @test
     */
    public function it_lists_all_the_prs(): void
    {
        $this->PRRepository->save(
            PR::fromNormalized([
                    'IDENTIFIER'  => 'akeneo/pim-community-dev/1111',
                    'GTMS'        => 1,
                    'NOT_GTMS'    => 1,
                    'COMMENTS'    => 1,
                    'CI_STATUS'   => 'PENDING',
                    'IS_MERGED'   => true,
                    'MESSAGE_IDS' => ['1', '2'],
                ]
            )
        );
        $this->PRRepository->save(
            PR::fromNormalized([
                    'IDENTIFIER'  => 'akeneo/pim-community-dev/2222',
                    'GTMS'        => 1,
                    'NOT_GTMS'    => 1,
                    'COMMENTS'    => 1,
                    'CI_STATUS'   => 'PENDING',
                    'IS_MERGED'   => false,
                    'MESSAGE_IDS' => ['1', '2'],

                ]
            )
        );

        $client = static::createClient();
        $client->request('GET', '/');
        $response = $client->getResponse();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals([
            [
                'IDENTIFIER'  => 'akeneo/pim-community-dev/2222',
                'GTMS'        => 1,
                'NOT_GTMS'    => 1,
                'COMMENTS'    => 1,
                'CI_STATUS'   => 'PENDING',
                'IS_MERGED'   => false,
                'MESSAGE_IDS' => ['1', '2'],

            ],
            [
                'IDENTIFIER'  => 'akeneo/pim-community-dev/1111',
                'GTMS'        => 1,
                'NOT_GTMS'    => 1,
                'COMMENTS'    => 1,
                'CI_STATUS'   => 'PENDING',
                'IS_MERGED'   => true,
                'MESSAGE_IDS' => ['1', '2'],

            ]
        ], json_decode($response->getContent(), true));
    }
}
