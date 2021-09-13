<?php

declare(strict_types=1);

namespace Test\Support;

use Ramsey\Uuid\Nonstandard\Uuid;
use Ramsey\Uuid\UuidInterface;
use SensitizedEventStore\Dbal\SensitiveDataManager\Domain\Aggregate\AggregateKey;
use SensitizedEventStore\Dbal\SensitiveDataManager\Domain\Aggregate\AggregateKeys;

class InMemoryAggregateKeys implements AggregateKeys
{
    /** @var array<string, AggregateKey> */
    private array $aggregateKeys = [];

    public function add(AggregateKey $aggregateKey): void
    {
        $this->aggregateKeys[(string) $aggregateKey->aggregateId()] = $aggregateKey;
    }

    public function withAggregateId(UuidInterface $aggregateId): ?AggregateKey
    {
        foreach ($this->aggregateKeys as $storedAggregateId => $aggregateKey) {
            if ($aggregateId->equals(Uuid::fromString($storedAggregateId))) {
                return $aggregateKey;
            }
        }

        return null;
    }

    public function update(AggregateKey $aggregateKey): void
    {
        $this->aggregateKeys[(string) $aggregateKey->aggregateId()] = $aggregateKey;
    }
}
