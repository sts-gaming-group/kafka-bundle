<?php

declare(strict_types=1);

namespace StsGamingGroup\KafkaBundle\Client\Consumer\Factory;

use RdKafka\Message as RdKafkaMessage;
use StsGamingGroup\KafkaBundle\Client\Consumer\Message;
use StsGamingGroup\KafkaBundle\Configuration\ResolvedConfiguration;
use StsGamingGroup\KafkaBundle\Configuration\Type\Decoder;
use StsGamingGroup\KafkaBundle\Configuration\Type\Denormalizer;
use StsGamingGroup\KafkaBundle\Decoder\Contract\DecoderInterface;
use StsGamingGroup\KafkaBundle\Denormalizer\Contract\DenormalizerInterface;
use StsGamingGroup\KafkaBundle\Validator\Validator;

class MessageFactory
{
    /**
     * @var array<DecoderInterface>
     */
    private array $decoders;

    /**
     * @var array<DenormalizerInterface>
     */
    private array $denormalizers;

    private Validator $validator;

    public function __construct(iterable $decoders, iterable $denormalizers, Validator $validator)
    {
        foreach ($decoders as $decoder) {
            $this->decoders[get_class($decoder)] = $decoder;
        }
        foreach ($denormalizers as $denormalizer) {
            $this->denormalizers[get_class($denormalizer)] = $denormalizer;
        }

        $this->validator = $validator;
    }

    public function create(RdKafkaMessage $rdKafkaMessage, ResolvedConfiguration $configuration): Message
    {
        $requiredDecoder = $configuration->getValue(Decoder::NAME);
        $decoded = $this->decoders[$requiredDecoder]->decode($configuration, $rdKafkaMessage->payload);

        $this->validator->validate($configuration, $decoded, Validator::PRE_DENORMALIZE_TYPE);

        $requiredDenormalizer = $configuration->getValue(Denormalizer::NAME);
        $denormalized = $this->denormalizers[$requiredDenormalizer]->denormalize($decoded);

        $this->validator->validate($configuration, $denormalized, Validator::POST_DENORMALIZE_TYPE);

        return new Message(
            $rdKafkaMessage->topic_name,
            $rdKafkaMessage->partition,
            $rdKafkaMessage->payload,
            $rdKafkaMessage->offset,
            $denormalized,
            $rdKafkaMessage->key
        );
    }
}
