[[_TOC_]]

## General info

High level Apache Kafka consumer/producer

## Technologies

- PHP >=7.4
- ext-rdkafka for PHP
- symfony components: refer to composer.json `require` section for required package versions


## Setup

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
  consumers:
    instances:
      App\Consumers\ExampleConsumer:
        brokers: [ '172.25.0.201:9092', '172.25.0.202:9092', '172.25.0.203:9092' ]
        schema_registry: 'http://172.25.0.201:8081'
        group_id: 'sts_kafka_test'
        topics: [ 'testing.dwh_kafka.tab_tickets_prematch' ]
  producers:    
    instances:
      App\Producers\ExampleProducer:
        brokers: [ '172.25.0.201:9092', '172.25.0.202:9092', '172.25.0.203:9092' ]    
        producer_topic: 'my_app_failed_message_topic'
   ```
3. Most of the time you would like to keep your kafka configuration in sts_kafka.yaml, but you can also pass configuration directly in CLI for example:
```
bin/console kafka:consumers:consume example_consumer --group_id some_other_group_id
```

Currently, options passed in CLI only work for consumers which are run by command `kafka:consumers:consume`.

The configurations are resolved in runtime. The priority is as follows:

- Configurations passed in CLI will always take precedence
- Configurations passed per consumer/producer basis (```instances:``` section in `consumers:` or `producers:` in sts_kafka.yaml)

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

## Validators

After of before denormalization, you may want to validate if given object should be passed to your consumer - you may want, for example, to filter out ticket states that are useless i.e. UNRESOLVED state.

1. Create validator
```php
<?php

declare(strict_types=1);

namespace App\Validator;

use App\Normalizer\MessageDTO;
use Sts\KafkaBundle\Validator\Contract\ValidatorInterface;
use Sts\KafkaBundle\Validator\Validator;

class TicketStateValidator implements ValidatorInterface
{
    public function validate($denormalized): bool
    {
        /** @var MessageDTO $denormalized */

        return $denormalized->getTicketState() === 'NON-WINNING';
    }

    public function failureReason($denormalized): string
    {
        /** @var MessageDTO $denormalized */

        return sprintf('Non-winning ticket expected. Got %s', $denormalized->getTicketState());
    }
    
    public function type() : string
    {
       // return Validator::PRE_DENORMALIZE_TYPE;
       // return Validator::POST_DENORMALIZE_TYPE;
    }
}
```
Register it in your configuration:
```yaml
sts_kafka:
  consumers:
    instances:
      App\Consumers\ExampleConsumer:
        validators: 
         - App\Validator\TicketStateValidator
         - App\Validator\ClientAlreadyBonusedValidator      
```
You may have multiple validators attached to one consumer. The priority of called validators is exactly how you defined them in sts_kafka.yaml - 
so in this case TicketStateValidator is called first, and then ClientAlreadyBonusedValidator is called.

If a validator returns false, an instance of ValidatorException is thrown. 
```php
 ...
 
 use Sts\KafkaBundle\Exception\ValidationException;
 
 public function handleException(KafkaException $exception, Context $context): bool
 {
     $thrown = $exception->getThrowable();
     if ($thrown instanceof ValidationException) {
         /** @var MessageDTO $denormalized */
         $denormalized = $thrown->getData();
         $this->logger->info(
             sprintf(
                 'Message has not passed validation for client %s. Reason %s', 
                 $denormalized->getClientId(),
                 $thrown->getFailedReason())
         );

         return true;
     }
 }
```
### Offset for a message which has not passed validation is committed automatically.

## Kafka Callbacks

Librdkafka (C/C++ library used underneath PHP) provides several callbacks that you can use in different situations (consuming/producing/error handling/logging). 
Your consumer must implement CallableInterface which requires you to define `callbacks` method. This method should return an array
of callbacks you wish to handle yourself.

```php
<?php

declare(strict_types=1);

namespace App\Consumers;

use Sts\KafkaBundle\Client\Contract\CallableInterface;
use Sts\KafkaBundle\Client\Contract\ConsumerInterface;use Sts\KafkaBundle\RdKafka\Callbacks;

class ExampleConsumer implements ConsumerInterface, CallableInterface
{
    public function callbacks(): array
    {
        return [
            Callbacks::OFFSET_COMMIT_CALLBACK => static function (
                \RdKafka\KafkaConsumer $kafkaConsumer,
                int $error,
                array $partitions
            ) {
                // call some action according to i.e. error
            },
            Callbacks::LOG_CALLBACK => static function ($kafka, int $level, string $facility, string $message) {
                // log it somewhere
            }
        ];
    }
    
    // other methods
}
 
```

## Producing Messages

1. To produce messages you must configure few options in sts_kafka.yaml:
```yaml 
producers:
 instances:
   App\Producers\ExampleProducer:
     brokers: [ '172.25.0.201:9092', '172.25.0.202:9092', '172.25.0.203:9092' ]
     producer_topic: 'topic_i_want_to_produce_to' #only one topic allowed per producer
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
 public function __construct(ProducerClient $producerClient, TicketRepository $ticketRepository)
 {
     $this->producerClient = $producerClient;
     $this->ticketRepository = $ticketRepository;
 }
 
 protected function execute(InputInterface $input, OutputInterface $output): int
 {
     $tickets = $this->ticketRepository->findAll();
     foreach ($tickets as $ticket) {
         $this->producerClient->produce($ticket);
     }

     $this->producerClient->flush(); // call flush after produce() method has finished

     return Command::SUCCESS;
 }
```
You can also set callbacks array to ProducerClient for example to check if messages were sent successfully. Your producer class should implement CallableInterface.
```php
use Sts\KafkaBundle\Client\Contract\CallableInterface;
use Sts\KafkaBundle\Client\Contract\ProducerInterface;

class ExampleProducer implements ProducerInterface, CallableInterface
{
    public function callbacks(): array
    {
        // callbacks array just like in Consumer example
    }
}
```
Other options that can be configured for ProducerClient at runtime:
```php 
$this->producerClient
   ->setPollingBatch(25000)   
   ->setPollingTimeoutMs(1000)
   ->setFlushTimeoutMs(500)
   ->setMaxFlushRetries(10);
```
- polling batch - after how many messages (in case of a loop, as in example above with $tickets) ProducerClient should call librdkafka `poll` method.
If you produce big messages and do not call poll frequently there might be an issue of librdkafka full internal queue. Also, consumers will not receive anything until `poll` has been called.
  So it is recommended to keep polling batch number at reasonable level i.e. 10000 or 20000
- polling timeout ms - how long librdkafka will wait until polling of a message finishes
- flush timeout ms, max flush retries - after calling `flush()` ProducerClient will try to flush remaining messages in librdkafka internal queue. Remaining messages are those who have not been `poll`ed yet.

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

## Showing current consumer/producer configuration

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
