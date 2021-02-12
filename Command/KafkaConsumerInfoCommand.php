<?php

declare(strict_types=1);

namespace Sts\KafkaBundle\Command;

use Sts\KafkaBundle\Consumer\Contract\ConsumerInterface;
use Sts\KafkaBundle\Consumer\ConsumerProvider;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class KafkaConsumerInfoCommand extends Command
{
    protected static $defaultName = 'kafka:consumers:info';

    private ConsumerProvider $consumerProvider;

    public function __construct(ConsumerProvider $consumerProvider)
    {
        $this->consumerProvider = $consumerProvider;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName('kafka:consumers:info')->setDescription('Shows information about available kafka consumers.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $table = new Table($output);
        $table->setHeaders(['Class', 'Type', 'Topics']);
        $table->setStyle('box');

        $consumers = $this->consumerProvider->getConsumers();
        if (!$consumers) {
            $output->writeln(sprintf(
                    'No registered consumers. Add one by implementing %s',
                    ConsumerInterface::class)
            );

            return Command::SUCCESS;
        }

        foreach ($consumers as $consumer) {
            $table->addRow([
                get_class($consumer),
                $consumer->getSupportedType(),
                implode(PHP_EOL, $consumer->getSupportedTopics())
            ]);
        }

        $table->render();

        return Command::SUCCESS;
    }
}
