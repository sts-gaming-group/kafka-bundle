<?php

declare(strict_types=1);

namespace Sts\KafkaBundle\Factory;

use RdKafka\Message as RdKafkaMessage;
use Sts\KafkaBundle\Configuration\ResolvedConfiguration;
use Sts\KafkaBundle\Client\Consumer\Message;
use Sts\KafkaBundle\Configuration\Type\Decoder;
use Sts\KafkaBundle\Configuration\Type\Denormalizer;
use Sts\KafkaBundle\Configuration\Type\Validators;
use Sts\KafkaBundle\Decoder\Contract\DecoderInterface;
use Sts\KafkaBundle\Denormalizer\Contract\DenormalizerInterface;
use Sts\KafkaBundle\Exception\ValidationException;
use Sts\KafkaBundle\Validator\Contract\ValidatorInterface;

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

    /**
     * @var array<ValidatorInterface>
     */
    private array $validators;

    public function __construct(iterable $decoders, iterable $denormalizers, iterable $validators)
    {
        foreach ($decoders as $decoder) {
            $this->decoders[get_class($decoder)] = $decoder;
        }
        foreach ($denormalizers as $denormalizer) {
            $this->denormalizers[get_class($denormalizer)] = $denormalizer;
        }
        foreach ($validators as $validator) {
            $this->validators[get_class($validator)] = $validator;
        }
    }

    public function create(RdKafkaMessage $rdKafkaMessage, ResolvedConfiguration $configuration): Message
    {
        $requiredDecoder = $configuration->getValue(Decoder::NAME);
        $decoded = $this->decoders[$requiredDecoder]->decode($configuration, $rdKafkaMessage->payload);

        $requiredDenormalizer = $configuration->getValue(Denormalizer::NAME);
        $denormalized = $this->denormalizers[$requiredDenormalizer]->denormalize($decoded);

        $requiredValidators = $configuration->getValue(Validators::NAME);
        foreach ($requiredValidators as $requiredValidator) {
            if (!$this->validators[$requiredValidator]->validate($denormalized)) {
                throw new ValidationException(
                    $this->validators[$requiredValidator],
                    $this->validators[$requiredValidator]->failureReason($denormalized),
                    sprintf('Validation not passed by %s', $requiredValidator)
                );
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
