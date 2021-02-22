<?php

declare(strict_types=1);

namespace Sts\KafkaBundle\Factory;

use Sts\KafkaBundle\Configuration\ConfigurationContainer;
use Sts\KafkaBundle\Configuration\Type\Decoder;
use Sts\KafkaBundle\Decoder\Contract\DecoderInterface;
use Sts\KafkaBundle\Exception\InvalidDecoderException;

class DecoderFactory
{
    /**
     * @var array<DecoderInterface>
     */
    private array $decoders = [];

    public function addDecoder(DecoderInterface $decoder): self
    {
        $this->decoders[] = $decoder;

        return $this;
    }

    public function create(ConfigurationContainer $configuration): DecoderInterface
    {
        $requiredDecoderClass = $configuration->getConfiguration(Decoder::NAME);
        foreach ($this->decoders as $decoder) {
            if ($requiredDecoderClass === get_class($decoder)) {
                return $decoder;
            }
        }

        throw new InvalidDecoderException(sprintf('Unknown decoder %s', $requiredDecoderClass));
    }
}
