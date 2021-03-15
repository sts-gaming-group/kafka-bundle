<?php

declare(strict_types=1);

namespace Sts\KafkaBundle\Factory;

use RdKafka\Message as RdKafkaMessage;
use Sts\KafkaBundle\Configuration\ResolvedConfiguration;
use Sts\KafkaBundle\Client\Consumer\Message;
use Sts\KafkaBundle\Configuration\Type\Denormalizer;
use Sts\KafkaBundle\Decoder\Contract\DecoderInterface;
use Sts\KafkaBundle\Denormalizer\Contract\DenormalizerInterface;

class MessageFactory
{
    private DecoderFactory $decoderFactory;
    private ?DecoderInterface $decoder = null;
    /**
     * @var array<DenormalizerInterface>
     */
    private iterable $denormalizers;

    public function __construct(DecoderFactory $decoderFactory, iterable $denormalizers)
    {
        $this->decoderFactory = $decoderFactory;
        $this->denormalizers = $denormalizers;
    }

    public function create(RdKafkaMessage $rdKafkaMessage, ResolvedConfiguration $resolvedConfiguration): Message
    {
        if (!$this->decoder) {
            $this->decoder = $this->decoderFactory->create($resolvedConfiguration);
        }

        $decodedPayload = $this->decoder->decode($resolvedConfiguration, $rdKafkaMessage->payload);
        $denormalized = null;

        foreach ($this->denormalizers as $denormalizer) {
            if (get_class($denormalizer) === $resolvedConfiguration->getConfigurationValue(Denormalizer::NAME)) {
                $denormalized = $denormalizer->denormalize($decodedPayload);
            }
        }

        return new Message(
            $rdKafkaMessage->topic_name,
            $rdKafkaMessage->partition,
            $rdKafkaMessage->payload,
            $rdKafkaMessage->key,
            $rdKafkaMessage->offset,
            $denormalized ?: $decodedPayload
        );
    }
}
