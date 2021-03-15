<?php

declare(strict_types=1);

namespace Sts\KafkaBundle\Client\Producer;

use Sts\KafkaBundle\Client\Contract\ProducerHandlerInterface;
use Sts\KafkaBundle\Exception\ProducerProviderException;

class ProducerHandlerProvider
{
    /**
     * @var array<ProducerHandlerInterface>
     */
    protected array $handlers = [];

    public function addHandler(ProducerHandlerInterface $handler): self
    {
        $this->handlers[] = $handler;

        return $this;
    }

    /**
     * @param $message
     * @return ProducerHandlerInterface
     */
    public function provide($message): ProducerHandlerInterface
    {
        $handlers = [];

        foreach ($this->handlers as $handler) {
            if ($handler->supports(get_class($message))) {
                $handlers[] = $handler;
            }
        }

        if (count($handlers) > 1) {
            throw new ProducerProviderException(sprintf(
                'Multiple handlers found for message %s',
                get_class($message)
            ));
        }

        if (!$handlers) {
            throw new ProducerProviderException(sprintf(
                'There is no matching handler for message %s.',
                get_class($message)
            ));
        }

        return $handlers[0];
    }
}
