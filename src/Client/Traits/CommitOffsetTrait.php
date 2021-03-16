<?php

declare(strict_types=1);

namespace Sts\KafkaBundle\Client\Traits;

use RdKafka\Message as RdKafkaMessage;
use Sts\KafkaBundle\Client\Consumer\Message;
use Sts\KafkaBundle\Configuration\Type\EnableAutoOffsetStore;
use Sts\KafkaBundle\Exception\InvalidConfigurationException;
use Sts\KafkaBundle\RdKafka\Context;

trait CommitOffsetTrait
{
    /**
     * @param RdKafkaMessage|Message $message
     * @param Context $context
     * @return bool
     */
    public function commitOffset($message, Context $context): bool
    {
        if (!$message instanceof RdKafkaMessage && !$message instanceof Message) {
            throw new \RuntimeException(
                sprintf(
                    'You have to pass %s or %s object.',
                    RdKafkaMessage::class,
                    Message::class
                )
            );
        }

        if ($this->canCommitOffset($context)) {
            if ($message instanceof RdKafkaMessage) {
                $topicName = $message->topic_name;
                $partition = $message->partition;
                $offset = $message->offset;
            }

            if ($message instanceof Message) {
                $topicName = $message->getTopicName();
                $partition = $message->getPartition();
                $offset = $message->getOffset();
            }
            $rdKafkaConsumerTopic = $context->getRdKafkaConsumerTopicByName($topicName);
            $rdKafkaConsumerTopic->offsetStore($partition, $offset);
        }

        return true;
    }

    private function canCommitOffset(Context $context): bool
    {
        if ($context->getConfigurationValue(EnableAutoOffsetStore::NAME) === 'true') {
            throw new InvalidConfigurationException(sprintf(
                'Unable to manually commit offset when %s configuration is set to `true`.',
                EnableAutoOffsetStore::NAME
            ));
        }

        return true;
    }
}
