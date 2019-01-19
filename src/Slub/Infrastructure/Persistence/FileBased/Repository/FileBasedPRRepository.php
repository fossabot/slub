<?php

declare(strict_types=1);

namespace Slub\Infrastructure\Persistence\FileBased\Repository;

use Slub\Domain\Entity\PR\PR;
use Slub\Domain\Entity\PR\PRIdentifier;
use Slub\Domain\Repository\PRNotFoundException;
use Slub\Domain\Repository\PRRepositoryInterface;

class FileBasedPRRepository implements PRRepositoryInterface
{
    /** @var string */
    private $filePath;

    public function __construct(string $filePath)
    {
        $this->filePath = $filePath;
    }

    public function save(PR $pr): void
    {
        $allPRs = $this->all();
        $allPRs[] = $pr;
        $this->saveAll($allPRs);
    }

    public function getBy(PRIdentifier $identifier): PR
    {
        $allPRs = $this->all();
        $result = $this->findPR($identifier, $allPRs);

        if (null === $result) {
            throw PRNotFoundException::create($identifier);
        }

        return $result;
    }

    public function resetFiles(): void
    {
        unlink($this->filePath);
    }

    /**
     * @return PR[]
     */
    private function all(): array
    {
        $normalizedPRs = $this->readFile();
        $result = $this->denormalizePRs($normalizedPRs);

        return $result;
    }

    /**
     * @return PR[]
     */
    private function denormalizePRs(array $normalizedPRs): array
    {
        return array_map(
            function (array $normalizedPR) {
                return PR::fromNormalized($normalizedPR);
            },
            $normalizedPRs
        );
    }

    /**
     * @param PR[] $allPRs
     */
    private function saveAll(array $allPRs): void
    {
        $normalizedAllPRs = $this->normalizePRs($allPRs);
        $this->writeFile($normalizedAllPRs);
    }

    /**
     * @param PR[] $prs
     *
     * @return array
     */
    private function normalizePRs(array $prs): array
    {
        return array_map(
            function (PR $pr) {
                return $pr->normalize();
            },
            $prs
        );
    }

    private function readFile(): array
    {
        if (!file_exists($this->filePath)) {
            return [];
        }
        $fileContent = file_get_contents($this->filePath);
        if (empty($fileContent)) {
            return [];
        }
        $result = json_decode($fileContent, true);

        return $result;
    }

    private function writeFile(array $normalizedAllPRs): void
    {
        if (!file_exists($this->filePath)) {
            touch($this->filePath);
        }

        $fp = fopen($this->filePath, 'w');
        if (false === $fp) {
            throw new \Exception(sprintf('Impossible to open the file at path "%s"', $this->filePath));
        }
        $serializedAllPRs = json_encode($normalizedAllPRs);
        if (false === $serializedAllPRs) {
            throw new \Exception('Impossible to serialize all PRs');
        }
        fwrite($fp, $serializedAllPRs);
        fclose($fp);
    }

    /**
     * @param PR[] $allPRs
     */
    private function findPR(PRIdentifier $identifier, array $allPRs): ?PR
    {
        $result = current(
            array_filter(
                $allPRs,
                function (PR $pr) use ($identifier) {
                    return $pr->identifier()->equals($identifier);
                }
            )
        );

        if (!$result) {
            return null;
        }

        return $result;
    }
}