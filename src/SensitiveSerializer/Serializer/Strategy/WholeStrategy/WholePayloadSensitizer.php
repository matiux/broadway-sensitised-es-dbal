<?php

declare(strict_types=1);

namespace Matiux\Broadway\SensitiveSerializer\Serializer\Strategy\WholeStrategy;

use BadMethodCallException;
use Matiux\Broadway\SensitiveSerializer\DataManager\Domain\Service\AggregateKeyManager;
use Matiux\Broadway\SensitiveSerializer\DataManager\Domain\Service\SensitiveDataManager;
use Matiux\Broadway\SensitiveSerializer\Serializer\Strategy\PayloadSensitizer;
use Matiux\Broadway\SensitiveSerializer\Serializer\ValueSerializer\ValueSerializer;
use Matiux\Broadway\SensitiveSerializer\Shared\Tools\Assert;
use Matiux\Broadway\SensitiveSerializer\Shared\Tools\Util;

final class WholePayloadSensitizer extends PayloadSensitizer
{
    private string $payloadIdKey;

    /** @var string[] */
    private array $excludedKeys;

    /**
     * @param SensitiveDataManager $sensitiveDataManager
     * @param AggregateKeyManager  $aggregateKeyManager
     * @param ValueSerializer      $valueSerializer
     * @param bool                 $automaticAggregateKeyCreation
     * @param string[]             $excludedKeys
     * @param string               $excludedIdKey
     */
    public function __construct(
        SensitiveDataManager $sensitiveDataManager,
        AggregateKeyManager $aggregateKeyManager,
        ValueSerializer $valueSerializer,
        bool $automaticAggregateKeyCreation = true,
        array $excludedKeys = ['occurred_at'],
        string $excludedIdKey = 'id'
    ) {
        parent::__construct($sensitiveDataManager, $aggregateKeyManager, $valueSerializer, $automaticAggregateKeyCreation);

        $this->payloadIdKey = $excludedIdKey;
        $this->excludedKeys = $excludedKeys;
    }

    /**
     * @return string[]
     */
    public function excludedKeys(): array
    {
        return $this->excludedKeys;
    }

    /**
     * @return array
     */
    protected function generateSensitizedPayload(): array
    {
        $toSensitize = $this->getPayload();

        $this->validatePayload($toSensitize);
        $this->removeNotSensitiveKeys($toSensitize);
        $this->encryptPayloadRecursively($toSensitize);

        $sensitizedPayload = $toSensitize + $this->getPayload();
        ksort($sensitizedPayload);

        return $sensitizedPayload;
    }

    private function encryptPayloadRecursively(array &$toSensitize): void
    {
        foreach ($toSensitize as &$value) {
            if (!Util::isAssociativeArray($value)) {
                Assert::isSerializable($value);
                $value = $this->encryptValue($value);
            } else {
                Assert::isArray($value);
                $this->encryptPayloadRecursively($value);
            }
        }
    }

    /**
     * @return array
     */
    protected function generateDesensitizedPayload(): array
    {
        $sensibleData = $this->getPayload();

        $this->validatePayload($sensibleData);
        $this->removeNotSensitiveKeys($sensibleData);
        $this->decryptPayloadRecursively($sensibleData);

        $desensitizedPayload = $sensibleData + $this->getPayload();
        ksort($desensitizedPayload);

        return $desensitizedPayload;
    }

    private function decryptPayloadRecursively(array &$toDesensitize): void
    {
        foreach ($toDesensitize as &$value) {
            if (!Util::isAssociativeArray($value)) {
                Assert::string($value);
                $value = $this->decryptValue($value);
            } else {
                Assert::isArray($value);
                $this->decryptPayloadRecursively($value);
            }
        }
    }

    public function removeNotSensitiveKeys(array &$toSensitize): void
    {
        unset($toSensitize[$this->payloadIdKey]);

        foreach ($this->excludedKeys as $excludedKey) {
            if (array_key_exists($excludedKey, $toSensitize)) {
                unset($toSensitize[$excludedKey]);
            }
        }
    }

    public function supports($subject): bool
    {
        throw new BadMethodCallException();
    }

    /**
     * @psalm-assert array{id: string} $payload
     */
    private function validatePayload(array $payload): void
    {
        Assert::keyExists(
            $payload,
            $this->payloadIdKey,
            sprintf("Key '%s' should be set in payload when using `WholePayloadSensitizer` strategy.", $this->payloadIdKey)
        );
    }
}
