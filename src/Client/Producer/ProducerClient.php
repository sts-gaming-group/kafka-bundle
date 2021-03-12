<?php

declare(strict_types=1);

namespace Sts\KafkaBundle\Client\Producer;

use RdKafka\Producer as RdKafkaProducer;
use Sts\KafkaBundle\Client\Contract\ProducerMessageInterface;
use Sts\KafkaBundle\Configuration\Type\ProducerPartition;
use Sts\KafkaBundle\Traits\CheckForRdKafkaExtensionTrait;
use Symfony\Component\Console\Input\InputInterface;

class ProducerClient
{
    use CheckForRdKafkaExtensionTrait;

    private ProducerProvider $producerProvider;
    private InMemoryProducerClientCache $producerClientCache;
    private ?\Closure $pollingCallback = null;

    public function __construct(ProducerProvider $producerProvider, InMemoryProducerClientCache $producerClientCache)
    {
        $this->producerProvider = $producerProvider;
        $this->producerClientCache = $producerClientCache;
    }

    public function produce(ProducerMessageInterface $message, ?InputInterface $input = null): self
    {
        $this->isKafkaExtensionLoaded();

        foreach ($this->producerProvider->getProducers() as $producer) {
            $producerClass = get_class($producer);
            if ($message->supportedBy($producerClass)) {
                $resolvedConfiguration = $this->producerClientCache->getResolvedConfiguration($producerClass, $input);
                $producer->setMessage($message);

                $conf = $this->producerClientCache->getRdKafkaConfiguration($producerClass, $resolvedConfiguration);
                $rdKafkaProducer = $this->producerClientCache->getRdKafkaProducer($producerClass, $conf);

                $topics = $this->producerClientCache->getProducerTopics(
                    $producerClass,
                    $resolvedConfiguration,
                    $rdKafkaProducer
                );

                $message = $producer->getMessage();
                foreach ($topics as $topic) {
                    $topic->produce(
                        $resolvedConfiguration->getConfigurationValue(ProducerPartition::NAME),
                        0,
                        $message->getPayload(),
                        $message->getKey()
                    );
                }

                $pollingCallback = $this->getPollingCallback();
                $pollingCallback($rdKafkaProducer->getOutQLen(), $rdKafkaProducer);

                return $this;
            }
        }

        throw new \RuntimeException('Message is not supported by any producer');
    }

    public function setPollingCallback(callable $callable): self
    {
        $this->pollingCallback = $callable;

        return $this;
    }

    private function getPollingCallback(): \Closure
    {
        if (!$this->pollingCallback) {
            return static function (int $queueLength, RdKafkaProducer $producer) {
                if ($queueLength % 50 === 0) {
                    while ($producer->getOutQLen() > 0) {
                        $producer->poll(0);
                    }
                }
            };
        }

        return $this->pollingCallback;
    }
}
