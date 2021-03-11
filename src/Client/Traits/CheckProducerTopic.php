<?php

declare(strict_types=1);

namespace Sts\KafkaBundle\Client\Traits;

use Sts\KafkaBundle\Client\Producer\BlacklistTopics;
use Sts\KafkaBundle\Exception\BlacklistTopicException;

trait CheckProducerTopic
{
    private array $topicsCache = [];

    public function isTopicBlacklisted(string $topic): void
    {
        if (!$this->topicsCache) {
            $this->topicsCache = (new BlacklistTopics())->getTopics();
        }

        if (in_array($topic, $this->topicsCache, true)) {
            throw new BlacklistTopicException(
                sprintf('Unable to produce to topic %s. It is blacklisted.', $topic)
            );
        }
    }
}
