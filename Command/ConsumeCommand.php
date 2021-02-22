<?php

declare(strict_types=1);

namespace Sts\KafkaBundle\Command;

use Sts\KafkaBundle\Configuration\ConfigurationContainer;
use Sts\KafkaBundle\Configuration\ConfigurationResolver;
use Sts\KafkaBundle\Configuration\RawConfigurations;
use Sts\KafkaBundle\Consumer\Client\ConsumerClient;
use Sts\KafkaBundle\Consumer\ConsumerProvider;
use Sts\KafkaBundle\Consumer\Contract\ConsumerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ConsumeCommand extends Command
{
    protected static $defaultName = 'kafka:consumers:consume';

    private RawConfigurations $configurations;
    private ConsumerProvider $consumerProvider;
    private ConfigurationResolver $configurationResolver;
    private ConsumerClient $consumerClient;

    public function __construct(
        RawConfigurations $configurations,
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
        $configuration = $this->configurationResolver->resolve($consumer, $input);

        if ($input->getOption('describe')) {
            $this->describeConsumer($configuration, $output, $consumer);

            return Command::SUCCESS;
        }
        $this->consumerClient->consume($consumer, $configuration);

        return Command::SUCCESS;
    }

    private function describeConsumer(
        ConfigurationContainer $configurations,
        OutputInterface $output,
        ConsumerInterface $consumer
    ): void {
        $table = new Table($output);
        $table->setHeaders(['configuration', 'value']);
        $table->setStyle('box');
        $values['consumer_name'] = $consumer->getName();

        foreach ($configurations->getConfigurations() as $name => $configuration) {
            if (is_array($configuration)) {
                $values[$name] = implode(', ', $configuration);
            } elseif ($configuration === true) {
                $values[$name] = 'true';
            } elseif ($configuration === false) {
                $values[$name] = 'false';
            } else {
                $values[$name] = $configuration;
            }
        }

        foreach ($values as $name => $value) {
            $table->addRow([$name, $value]);
        }

        $table->render();
    }
}
