<?php

declare(strict_types=1);

namespace Sts\KafkaBundle\Command;

use Sts\KafkaBundle\Client\Producer\ProducerProvider;
use Sts\KafkaBundle\Command\Traits\DescribeTrait;
use Sts\KafkaBundle\Configuration\ConfigurationResolver;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ProducersDescribeCommand extends Command
{
    use DescribeTrait;

    protected static $defaultName = 'kafka:producers:describe';

    private ProducerProvider $producerProvider;
    private ConfigurationResolver $configurationResolver;

    public function __construct(
        ProducerProvider $producerProvider,
        ConfigurationResolver $configurationResolver
    ) {
        $this->producerProvider = $producerProvider;
        $this->configurationResolver = $configurationResolver;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('Show producers configuration.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $producers = $this->producerProvider->getProducers();

        foreach ($producers as $producer) {
            $this->describe($this->configurationResolver->resolve($producer), $output, $producer);
        }

        return 0;
    }
}
