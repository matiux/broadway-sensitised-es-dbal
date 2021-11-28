<?php

declare(strict_types=1);

namespace Matiux\Broadway\SensitiveSerializer\DataManager\Domain\Service;

use Matiux\Broadway\SensitiveSerializer\DataManager\Domain\Aggregate\AggregateKey;
use Matiux\Broadway\SensitiveSerializer\DataManager\Domain\Aggregate\AggregateKeys;
use Matiux\Broadway\SensitiveSerializer\DataManager\Domain\Exception\AggregateKeyNotFoundException;
use Matiux\Broadway\SensitiveSerializer\DataManager\Domain\Exception\DuplicatedAggregateKeyException;
use Ramsey\Uuid\UuidInterface;

class AggregateKeyManager
{
    private KeyGenerator $keyGenerator;
    private AggregateKeys $aggregateKeys;
    private SensitiveDataManager $sensitiveDataManager;
    private string $aggregateMasterKey;

    public function __construct(
        KeyGenerator $keyGenerator,
        AggregateKeys $aggregateKeys,
        SensitiveDataManager $sensitiveDataManager,
        string $aggregateMasterKey
    ) {
        $this->keyGenerator = $keyGenerator;
        $this->aggregateKeys = $aggregateKeys;
        $this->sensitiveDataManager = $sensitiveDataManager;
        $this->aggregateMasterKey = $aggregateMasterKey;
    }

    /**
     * Returns the decrypted aggregate_key.
     * Persists the aggregate_key encrypted with the aggregate_master_key.
     *
     * @param UuidInterface $aggregateId
     *
     * @throws DuplicatedAggregateKeyException
     *
     * @return AggregateKey
     */
    public function createAggregateKey(UuidInterface $aggregateId): AggregateKey
    {
        $this->ensureDoesNotExist($aggregateId);

        // $encryptedKeyForAggregate represents encrypted encryption key for Aggregate (AGGREGATE_KEY)
        // encrypted with the AGGREGATE_MASTER_KEY
        $encryptedKeyForAggregate = $this->sensitiveDataManager->encrypt(
            $this->keyGenerator->generate(),
            $this->aggregateMasterKey
        );

        // AggregateKey model
        $aggregateKey = AggregateKey::create($aggregateId, $encryptedKeyForAggregate);
        $this->aggregateKeys->add($aggregateKey);

        return $aggregateKey;
    }

    /**
     * @param UuidInterface $aggregateId
     *
     * @throws DuplicatedAggregateKeyException
     */
    private function ensureDoesNotExist(UuidInterface $aggregateId): void
    {
        try {
            $this->obtainAggregateKeyOrFail($aggregateId);

            throw DuplicatedAggregateKeyException::create($aggregateId);
        } catch (AggregateKeyNotFoundException $exception) {
            return;
        }
    }

    /**
     * Reveals the decrypted aggregate_key.
     *
     * @param UuidInterface $aggregateId
     *
     * @throws AggregateKeyNotFoundException
     *
     * @return null|string
     */
    public function revealAggregateKey(UuidInterface $aggregateId): ?string
    {
        $aggregateKey = $this->obtainAggregateKeyOrFail($aggregateId);

        if ($aggregateKey->exists()) {
            return $this->sensitiveDataManager->decrypt((string) $aggregateKey, $this->aggregateMasterKey);
        }

        return null;
    }

    /**
     * Returns the AggregateKey for a specific id or throw an exception if aggregate not found.
     *
     * @param UuidInterface $aggregateId
     *
     * @throws AggregateKeyNotFoundException
     *
     * @return AggregateKey
     */
    public function obtainAggregateKeyOrFail(UuidInterface $aggregateId): AggregateKey
    {
        if (!$aggregateKey = $this->aggregateKeys->withAggregateId($aggregateId)) {
            throw AggregateKeyNotFoundException::create($aggregateId);
        }

        return $aggregateKey;
    }
}
