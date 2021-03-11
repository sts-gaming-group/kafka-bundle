<?php

declare(strict_types=1);

namespace Sts\KafkaBundle\Client\Traits;

use Sts\KafkaBundle\Client\Contract\MessageInterface;
use Sts\KafkaBundle\Configuration\ResolvedConfiguration;
use Sts\KafkaBundle\Configuration\Type\EnableAutoOffsetStore;
use Sts\KafkaBundle\Exception\InvalidConfigurationException;
use Sts\KafkaBundle\RdKafka\Context;

trait CommitOffsetTrait
{
    public function commitOffset(MessageInterface $message, Context $context): bool
    {
        if (!$this->canCommitOffset($context->getResolvedConfiguration())) {
            throw new InvalidConfigurationException(sprintf(
                'Unable to manually commit offset when %s configuration is set to `true`.',
                EnableAutoOffsetStore::NAME
            ));
        }
        $rdKafkaConsumerTopic = $context->getRdKafkaConsumerTopicByName($message->getTopicName());
        $rdKafkaConsumerTopic->offsetStore($message->getPartition(), $message->getOffset());

        return true;
    }

    private function canCommitOffset(ResolvedConfiguration $resolvedConfiguration): bool
    {
        return $resolvedConfiguration->getConfigurationValue(EnableAutoOffsetStore::NAME) === 'false';
    }
}
