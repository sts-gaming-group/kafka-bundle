<?php

declare(strict_types=1);

namespace Sts\KafkaBundle\Command;

use Sts\KafkaBundle\Client\Consumer\Message;
use Sts\KafkaBundle\Client\Contract\RetryProducerInterface;
use Sts\KafkaBundle\Client\Producer\ProducerClient;
use Sts\KafkaBundle\Client\Producer\ProducerProvider;
use Sts\KafkaBundle\Configuration\ConfigurationResolver;
use Sts\KafkaBundle\Configuration\RawConfiguration;
use Sts\KafkaBundle\Configuration\ResolvedConfiguration;
use Sts\KafkaBundle\Client\Consumer\ConsumerClient;
use Sts\KafkaBundle\Client\Consumer\ConsumerProvider;
use Sts\KafkaBundle\Client\Contract\ConsumerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ConsumeCommand extends Command
{
    protected static $defaultName = 'kafka:consumers:consume';

    private RawConfiguration $configurations;
    private ConsumerProvider $consumerProvider;
    private ConfigurationResolver $configurationResolver;
    private ConsumerClient $consumerClient;

    public function __construct(
        RawConfiguration $configurations,
        ConsumerProvider $consumerProvider,
        ConfigurationResolver $configurationResolver,
        ConsumerClient $consumerClient
    ) {
        $this->configurations = $configurations;
        $this->consumerProvider = $consumerProvider;
        $this->configurationResolver = $configurationResolver;
        $this->consumerClient = $consumerClient;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription(
            sprintf(
                'Starts consuming messages from kafka using class implementing %s.',
                ConsumerInterface::class
            )
        )
            ->addArgument('name', InputArgument::REQUIRED, 'Name of the registered consumer.')
            ->addOption('describe', null, InputOption::VALUE_NONE, 'Shows current consumer configuration');

        foreach ($this->configurations->getConfigurations() as $configuration) {
            $this->addOption(
                $configuration->getName(),
                null,
                $configuration->getMode(),
                $configuration->getDescription()
            );
        }
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $consumer = $this->consumerProvider->provide($input->getArgument('name'));
        $resolvedConfiguration = $this->configurationResolver->resolve($consumer, $input);

        if ($input->getOption('describe')) {
            $this->describeConsumer($resolvedConfiguration, $output, $consumer);

            return 0;
        }
        $this->consumerClient->consume($consumer, $resolvedConfiguration);

        return 0;
    }

    private function describeConsumer(
        ResolvedConfiguration $resolvedConfiguration,
        OutputInterface $output,
        ConsumerInterface $consumer
    ): void {
        $table = new Table($output);
        $table->setHeaders(['configuration', 'value']);
        $table->setStyle('box');
        $values['consumer_name'] = $consumer->getName();

        foreach ($resolvedConfiguration->getConfigurations() as $name => $configuration) {
            $resolvedValue = $configuration['resolvedValue'];
            if (is_array($resolvedValue)) {
                $values[$name] = implode(', ', $resolvedValue);
            } elseif ($resolvedValue === true) {
                $values[$name] = 'true';
            } elseif ($resolvedValue === false) {
                $values[$name] = 'false';
            } else {
                $values[$name] = $resolvedValue;
            }
        }

        foreach ($values as $name => $value) {
            $table->addRow([$name, $value]);
        }

        $table->render();
    }
}
