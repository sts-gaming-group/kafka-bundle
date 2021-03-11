<?php

declare(strict_types=1);

namespace Sts\KafkaBundle\Factory;

use RdKafka\Message as RdKafkaMessage;
use Sts\KafkaBundle\Configuration\ResolvedConfiguration;
use Sts\KafkaBundle\Client\Message;
use Sts\KafkaBundle\Decoder\Contract\DecoderInterface;

class MessageFactory
{
    private DecoderFactory $decoderFactory;
    private ?DecoderInterface $decoder = null;

    public function __construct(DecoderFactory $decoderFactory)
    {
        $this->decoderFactory = $decoderFactory;
    }

    public function create(RdKafkaMessage $rdKafkaMessage, ResolvedConfiguration $resolvedConfiguration): Message
    {
        if (!$this->decoder) {
            $this->decoder = $this->decoderFactory->create($resolvedConfiguration);
        }

        $decodedPayload = $this->decoder->decode($resolvedConfiguration, $rdKafkaMessage->payload);

        return new Message(
            $rdKafkaMessage->topic_name,
            $rdKafkaMessage->partition,
            $rdKafkaMessage->payload,
            $rdKafkaMessage->key,
            $rdKafkaMessage->offset,
            $decodedPayload
        );
    }
}
