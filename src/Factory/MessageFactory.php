<?php

declare(strict_types=1);

namespace Sts\KafkaBundle\Factory;

use RdKafka\Message as RdKafkaMessage;
use Sts\KafkaBundle\Client\Contract\ConsumerMessageInterface;
use Sts\KafkaBundle\Configuration\ResolvedConfiguration;
use Sts\KafkaBundle\Client\ConsumerMessage;
use Sts\KafkaBundle\Decoder\Contract\DecoderInterface;

class MessageFactory
{
    private DecoderFactory $decoderFactory;
    private ?DecoderInterface $decoder = null;

    public function __construct(DecoderFactory $decoderFactory)
    {
        $this->decoderFactory = $decoderFactory;
    }

    public function create(
        RdKafkaMessage $rdKafkaMessage,
        ResolvedConfiguration $resolvedConfiguration
    ): ConsumerMessageInterface {
        if (!$this->decoder) {
            $this->decoder = $this->decoderFactory->create($resolvedConfiguration);
        }

        $decodedPayload = $this->decoder->decode($resolvedConfiguration, $rdKafkaMessage->payload);

        return new ConsumerMessage(
            $rdKafkaMessage->topic_name,
            $rdKafkaMessage->partition,
            $rdKafkaMessage->payload,
            $rdKafkaMessage->key,
            $rdKafkaMessage->offset,
            $decodedPayload
        );
    }
}
