<?php

declare(strict_types=1);

namespace Sts\KafkaBundle\Factory;

use RdKafka\Message as RdKafkaMessage;
use Sts\KafkaBundle\Configuration\ResolvedConfiguration;
use Sts\KafkaBundle\Client\Consumer\Message;
use Sts\KafkaBundle\Configuration\Type\Decoder;
use Sts\KafkaBundle\Configuration\Type\Denormalizer;
use Sts\KafkaBundle\Decoder\Contract\DecoderInterface;
use Sts\KafkaBundle\Denormalizer\Contract\DenormalizerInterface;

class MessageFactory
{
    /**
     * @var iterable<DecoderInterface>
     */
    private iterable $decoders;

    /**
     * @var iterable<DenormalizerInterface>
     */
    private iterable $denormalizers;

    public function __construct(iterable $decoders, iterable $denormalizers)
    {
        $this->decoders = $decoders;
        $this->denormalizers = $denormalizers;
    }

    public function create(RdKafkaMessage $rdKafkaMessage, ResolvedConfiguration $resolvedConfiguration): Message
    {
        $decoded = $denormalized = null;

        $requiredDecoder = $resolvedConfiguration->getConfigurationValue(Decoder::NAME);
        foreach ($this->decoders as $decoder) {
            if (get_class($decoder) === $requiredDecoder) {
                $decoded = $decoder->decode($resolvedConfiguration, $rdKafkaMessage->payload);
            }
        }

        $requiredDenormalizer = $resolvedConfiguration->getConfigurationValue(Denormalizer::NAME);
        foreach ($this->denormalizers as $denormalizer) {
            if (get_class($denormalizer) === $requiredDenormalizer) {
                $denormalized = $denormalizer->denormalize($decoded);
            }
        }

        return new Message(
            $rdKafkaMessage->topic_name,
            $rdKafkaMessage->partition,
            $rdKafkaMessage->payload,
            $rdKafkaMessage->key,
            $rdKafkaMessage->offset,
            $denormalized
        );
    }
}
