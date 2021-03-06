<?php

declare(strict_types=1);

namespace Slub\Domain\Entity\PR;

use Webmozart\Assert\Assert;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 */
class CIStatus
{
    private const PENDING = 'PENDING';
    private const GREEN = 'GREEN';
    private const RED = 'RED';

    /** @var string */
    private $status;

    private function __construct(string $status)
    {
        $this->status = $status;
    }

    public static function pending(): self
    {
        return new self(self::PENDING);
    }

    public static function green(): self
    {
        return new self(self::GREEN);
    }

    public static function red(): self
    {
        return new self(self::RED);
    }

    public static function fromNormalized(string $ciStatus): self
    {
        Assert::oneOf($ciStatus, [self::PENDING, self::GREEN, self::RED]);

        return new self($ciStatus);
    }

    public function stringValue(): string
    {
        return $this->status;
    }
}
