<?php

declare(strict_types=1);

namespace Sts\KafkaBundle\Client\Traits;

use Sts\KafkaBundle\Exception\BlacklistTopicException;

trait CheckProducerTopic
{
    public function isTopicBlacklisted(string $topic): bool
    {
        $blacklistedPatterns = [
            'dwh_kafka',
            'sts_internal'
        ];

        foreach ($blacklistedPatterns as $blacklistedPattern) {
            if (strpos($topic, $blacklistedPattern) !== false) {
                throw new BlacklistTopicException(
                    sprintf('Unable to produce to topic %s. It is blacklisted.', $topic)
                );
            }
        }

        return true;
    }
}
