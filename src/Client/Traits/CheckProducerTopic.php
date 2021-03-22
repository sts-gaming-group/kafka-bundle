<?php

declare(strict_types=1);

namespace Sts\KafkaBundle\Client\Traits;

use Sts\KafkaBundle\Exception\BlacklistTopicException;

trait CheckProducerTopic
{
    public function isTopicBlacklisted(string $topic): bool
    {
        if (strpos($topic, 'dwh_kafka') !== false) {
            throw new BlacklistTopicException(
                sprintf('Unable to produce to topic %s. It is blacklisted.', $topic)
            );
        }

        if (strpos($topic, 'sts_internal') !== false) {
            throw new BlacklistTopicException(
                sprintf('Unable to produce to topic %s. It is blacklisted.', $topic)
            );
        }

        return true;
    }
}
