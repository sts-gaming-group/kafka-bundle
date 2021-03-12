<?php

declare(strict_types=1);

namespace Sts\KafkaBundle\Client\Traits;

use RdKafka\Message as RdKafkaMessage;
use Sts\KafkaBundle\Client\Contract\ConsumerMessageInterface;
use Sts\KafkaBundle\Configuration\Type\EnableAutoOffsetStore;
use Sts\KafkaBundle\Exception\InvalidConfigurationException;
use Sts\KafkaBundle\RdKafka\Context;

trait CommitOffsetTrait
{
    public function commitOffset(ConsumerMessageInterface $message, Context $context): bool
    {
        if ($this->canCommitOffset($context)) {
            $rdKafkaConsumerTopic = $context->getRdKafkaConsumerTopicByName($message->getTopicName());
            $rdKafkaConsumerTopic->offsetStore($message->getPartition(), $message->getOffset());
        }

        return true;
    }

    public function commitFailedMessage(RdKafkaMessage $message, Context $context): bool
    {
        if ($this->canCommitOffset($context)) {
            $rdKafkaConsumerTopic = $context->getRdKafkaConsumerTopicByName($message->topic_name);
            $rdKafkaConsumerTopic->offsetStore($message->partition, $message->offset);
        }

        return true;
    }

    private function canCommitOffset(Context $context): bool
    {
        if ($context->getResolvedConfigurationValue(EnableAutoOffsetStore::NAME) === 'true') {
            throw new InvalidConfigurationException(sprintf(
                'Unable to manually commit offset when %s configuration is set to `true`.',
                EnableAutoOffsetStore::NAME
            ));
        }

        return true;
    }
}
