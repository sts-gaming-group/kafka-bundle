
High level Kafka consumer/producer
## Installation

Published versions of this package are available at https://gitlab.sts.pl/tech/kafka-bundle/-/packages

1. Create personal access token from GitLab

    - Sign in to GitLab.
    - In the top-right corner, select your avatar.
    - Select Edit profile.
    - In the left sidebar, select Access Tokens.
    - Choose a name and optional expiry date for the token.
    - Choose the desired scopes.
    - Select Create personal access token.

2. Add STS self-managed GitLab instance to composer

    - composer config gitlab-domains gitlab.sts.pl

3. Authorize to gitlab with auth.json in your project

    - composer config gitlab-token.gitlab.sts.pl <personal_access_token>

4. Add a repository to your project
    - composer config repositories.gitlab.sts.pl/26 '{"type": "composer", "url":"https://gitlab.sts.pl/api/v4/group/26/-/packages/composer/packages.json"}'

5. Install package with desired version
    - composer req sts/kafka-bundle:\<version>

## Basic Configuration

1. Add sts_kafka.yaml to config folder at \<root_folder>/config/packages/sts_kafka.yaml or in a specific env folder i.e. \<root_folder>/config/packages/prod/sts_kafka.yaml
2. Add configuration to sts_kafka.yaml for example:
 ```yaml
sts_kafka:
  consumers: #applies only to consumers
    brokers: [ '172.25.0.201:9092', '172.25.0.202:9092', '172.25.0.203:9092' ]
    schema_registry: 'http://172.25.0.201:8081'
    instances: #applies to specific consumer classes
      App\Consumers\ExampleConsumer:
        group_id: 'sts_kafka_test'
        topics: [ 'testing.dwh_kafka.tab_tickets_prematch' ]
  producers: #applies only to producers
    brokers: [ '172.25.0.201:9092', '172.25.0.202:9092', '172.25.0.203:9092' ]
      instances:
        App\Producers\ExampleProducer: #applies to specific producer classes
        topics: [ 'my_app_failed_message_topic' ]
   ```
3. Most of the time you would like to keep your kafka configuration in sts_kafka.yaml, but you can also pass configuration directly in CLI for example:
```
bin/console kafka:consumers:consume example_consumer --group_id some_other_group_id
```

The configurations are resolved in runtime. The priority is as follows:

- Configurations passed in CLI will always take precedence
- Configurations passed per consumer/producer basis (```instances:``` section in `consumers:` or `producers:` in sts_kafka.yaml) take precedence over global configuration in sts_kafka.yaml
- Global consumer/producer configuration (`consumers:` and `producers:` section in sts_kafka.yaml) 

## Consuming messages
1. Create consumer
```php
<?php

declare(strict_types=1);

namespace App\Consumers;

use Sts\KafkaBundle\Client\Consumer\Message;
use Sts\KafkaBundle\Client\Contract\ConsumerInterface;
use Sts\KafkaBundle\Exception\KafkaException;
use Sts\KafkaBundle\RdKafka\Context;

class ExampleConsumer implements ConsumerInterface
{
    public const CONSUMER_NAME = 'example_consumer';

    public function consume(Message $message, Context $context): bool
    {
        $data = $message->getData(); // contains denormalized data from Kafka
        $retryNo = $context->getRetryNo();  // contains retry count in case of a failure
        
        return true;
    }

    public function handleException(KafkaException $exception, Context $context): bool
    {
        $message = $exception->getMessage(); // contains exception message
        $throwable = $exception->getThrowable(); // contains last thrown object

        return true;
    }

    public function getName(): string
    {
        return self::CONSUMER_NAME; // consumer unique name in your project
    }
 }
 ```
 2. If configuration was done properly (proper broker, topic and most likely schema registry), you should be able to run your consumer and receive messages
 ```
 bin/console kafka:consumers:consume example_consumer
 ```
## Retrying failed messages

Any uncaught exception thrown inside `consume` method may trigger a retry if you'd like. If you wish to receive the same message again, configure the retry options in sts_kafka.yaml
```yaml
sts_kafka:
  consumers:
     ... # global consumers configurations
    instances:
      App\Consumers\ExampleConsumer:
        ... # other configurations
        max_retries: 3 # defaults to 0 which means it is disabled
        max_retry_delay: 2500 # defaults to 2000 ms
        retry_delay: 300 # defaults to 200 ms
        retry_multiplier: 3 # defaults to 2
```

With such configuration you will receive the same message 4 times maximum (first consumption + 3 retries). Before the first retry, there will be 300 ms delay.
Before the second retry, there will be 900 ms delay (retry_delay * retry_multiplier). Before the third retry, there will be 2500 ms delay (max_retry_delay).
`It is important to remember about committing offsets in Kafka in case of a permanently failed message.` More about it later.

## Decoders

Decoders are meant to turn raw Kafka data (json, avro, plain text or anything else) into PHP array (or actually any format you'd like). There are three decoders available:
- AvroDecoder
- JsonDecoder (which actually only does json_decode on kafka raw data)
- PlainDecoder (which actually does not decode the message but passes you a raw version of it)


By default, this package uses AvroDecoder and requires a schema_registry configuration. Schema registry should be a URL containing schema versions of consumed messages.


You can also implement your own decoder by implementing `DecoderInterface`
```php
<?php

namespace App\Decoder;

use Sts\KafkaBundle\Configuration\ResolvedConfiguration;
use Sts\KafkaBundle\Decoder\Contract\DecoderInterface;

class CustomDecoder implements DecoderInterface
{
    public function decode(ResolvedConfiguration $configuration, string $message)
    {
        // $configuration contains values from sts_kafka.yaml or CLI
        // $message contains raw value from Kafka
    }
}
```

Register it in your configuration
```yaml
sts_kafka:
  consumers:
    instances:
      App\Consumers\ExampleConsumer:
        decoder: App\Decoder\CustomDecoder
```

## Denormalizers

You may also want to denormalize the message into some kind of DTO or any other object you wish.
By default, this bundle does not denormalize the message into any object and passes you an array (which comes from the AvroDecoder).

Your denormalizers must implement DenormalizerInterface and requires you to implement `denormalize` method. Return value may be of any kind.
```php
<?php

declare(strict_types=1);

namespace App\Normalizer;

use Sts\KafkaBundle\Denormalizer\Contract\DenormalizerInterface;

class CustomDenormalizer implements DenormalizerInterface
{
    public function denormalize($data): MessageDTO
    {
        // $data is an array which comes from AvroDecoder or some other registered Decoder
        $messageDTO = new MessageDTO();
        $messageDTO->setTicketNumber($data['ticket_number']);

        return $messageDTO;
    }
}
```
Register it in your configuration:
```yaml
sts_kafka:
  consumers:
    instances:
      App\Consumers\ExampleConsumer:
        denormalizer: App\Normalizer\CustomDenormalizer
```

Receive it in your consumer:
```php
<?php

...

class ExampleConsumer implements ConsumerInterface
{
    public function consume(Message $message, Context $context): bool
    {
        $messageDTO = $message->getData(); // $messageDTO comes from CustomDenormalizer
        
        return true;
    }
```

## Producing Messages

1. To produce messages you must configure few options in sts_kafka.yaml:
```yaml 
producers:
 instances:
   App\Producers\ExampleProducer:
     brokers: [ '172.25.0.201:9092', '172.25.0.202:9092', '172.25.0.203:9092' ]
     topics: [ 'topic_i_want_to_produce_to' ]
```

2. Create data object which you want to work on (i.e. some entity or DTO)
```php 
<?php

declare(strict_types=1);

namespace App\Producers;

class Ticket
{
    private int $clientId;
    private string $ticketNumber;
    private int $totalBet;

    public function __construct(int $clientId, string $ticketNumber, int $totalBet)
    {
        $this->clientId = $clientId;
        $this->ticketNumber = $ticketNumber;
        $this->totalBet = $totalBet;
    }

    public function toArray(): array
    {
        return [
            'clientId' => $this->clientId,
            'ticketNumber' => $this->ticketNumber,
            'totalBet' => $this->totalBet
        ];
    }
}
```
3. Create a producer which will work on your data object and create Message for Kafka
```php
<?php

declare(strict_types=1);

namespace App\Producers;

use Sts\KafkaBundle\Client\Contract\ProducerInterface;
use Sts\KafkaBundle\Client\Producer\Message;

class ExampleProducer implements ProducerInterface
{
    public function produce($data): Message
    {
        /** @var Ticket $data */
        return new Message(json_encode($data->toArray()), $data->getClientId());
        // first argument of Message is the payload as a string
        // second argument is a message key which is used to help kafka partition messages
    }

    public function supports($data): bool
    {
        // in case of many producers you should check what $data is passed here
        return get_class($data )=== Ticket::class;
    }
}
```
4. Push message by calling ProducerClient::produce() i.e. somewhere in your Command class
```php
<?php

declare(strict_types=1);

namespace App\Command;

use Sts\KafkaBundle\Client\Producer\ProducerClient;

class ExampleCommand extends Command
{
   ...

 public function __construct(ProducerClient $producerClient, TicketRepository $ticketRepository)
 {
     $this->producerClient = $producerClient;
     $this->ticketRepository = $ticketRepository;
 }
 
 protected function execute(InputInterface $input, OutputInterface $output): int
 {
     $tickets = $this->ticketRepository->findAll();
     foreach ($tickets as $ticket) {
         $this->producerClient->produce($message);
     }

     $this->producerClient->flush(); // call flush after produce() method has finished

     return Command::SUCCESS;
 }
```
You can also set delivery callback to ProducerClient to check if messages were sent successfully.
```php
$this->producerClient->setDeliveryCallback(static function (RdKafka\Kafka $kafka, RdKafka\Message $message) {
   if ($message->err) {
      throw new \Exception('Message lost permanently. Let's produce again');
   }
});
 
```

## Custom configurations

Some times you may wish to pass some additional options to your Consumer object. You may add your own configuration:
```php
<?php

declare(strict_types=1);

namespace App\Configuration;

use Sts\KafkaBundle\Configuration\Contract\ConfigurationInterface;
use Symfony\Component\Console\Input\InputOption;

class Modulo implements ConfigurationInterface
{
    public function getName(): string
    {
        return 'modulo';
    }

    public function getMode(): int
    {
        return InputOption::VALUE_REQUIRED;
    }

    public function getDescription(): string
    {
        return 'Option description';
    }

    public function isValueValid($value): bool
    {
        return is_numeric($value) && $value > 0;
    }

    public static function getDefaultValue(): int
    {
        return 1;
    }
}
```
Custom option may only be passed in CLI
```
bin/console kafka:consumers:consume example_consumer --modulo 4
```
You will receive it in consume method, and you may take actions accordingly.
```php
class ExampleConsumer implements ConsumerInterface
{
    public const CONSUMER_NAME = 'example_consumer';

    public function consume(Message $message, Context $context): bool
    {
        $modulo = $context->getValue(Modulo::NAME);
    }
```

### Showing current consumer/producer configuration

You can show current configuration that will be passed to consumer by adding --describe to command
```
bin/console kafka:consumers:consume example_consumer --describe
┌───────────────────────────┬─────────────────────────────────────────────────────────┐
│ configuration             │ value                                                   │
├───────────────────────────┼─────────────────────────────────────────────────────────┤
│ class                     │ App\Consumers\ExampleConsumer                           │
│ topics                    │ testing.dwh_kafka.tab_tickets_prematch                  │
│ group_id                  │ sts_kafka_test                                          │
│ brokers                   │ 172.25.0.201:9092, 172.25.0.202:9092, 172.25.0.203:9092 │
│ offset_store_method       │ broker                                                  │
│ timeout                   │ 1000                                                    │
│ auto_offset_reset         │ smallest                                                │
│ auto_commit_interval_ms   │ 5                                                       │
│ decoder                   │ Sts\KafkaBundle\Decoder\AvroDecoder                     │
│ schema_registry           │ http://172.25.0.201:8081                                │
│ enable_auto_offset_store  │ true                                                    │
│ enable_auto_commit        │ true                                                    │
│ log_level                 │ 3                                                       │
│ register_missing_schemas  │ false                                                   │
│ register_missing_subjects │ false                                                   │
│ denormalizer              │ App\Normalizer\CustomDenormalizer                       │
│ max_retries               │ 3                                                       │
│ retry_delay               │ 250                                                     │
│ retry_multiplier          │ 3                                                       │
│ max_retry_delay           │ 3000                                                    │
└───────────────────────────┴─────────────────────────────────────────────────────────┘

```
You can show producers configuration by running
```
bin/console kafka:producers:describe

┌────────────────────┬─────────────────────────────────────────────────────────┐
│ configuration      │ value                                                   │
├────────────────────┼─────────────────────────────────────────────────────────┤
│ class              │ App\Producers\ExampleProducer                           │
│ brokers            │ 172.25.0.201:9092, 172.25.0.202:9092, 172.25.0.203:9092 │
│ log_level          │ 3                                                       │
│ producer_partition │ -1                                                      │
│ producer_topic     │ topic_i_want_to_produce_to                              │
└────────────────────┴─────────────────────────────────────────────────────────┘
```

# To be continued...
