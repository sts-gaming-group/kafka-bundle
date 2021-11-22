![kafka-bundle](https://user-images.githubusercontent.com/12221744/138563639-180ddfe2-a922-4fa5-a29a-d3140f50621d.png)

- [Technology stack](#technology-stack)
- [Quick start](#quick-start)
- [Example project](#example-project)
- [Basic Configuration](#basic-configuration)
- [Consuming messages](#consuming-messages)
- [Retrying failed messages](#retrying-failed-messages)
- [Handling offsets](#handling-offsets)
- [Decoders](#decoders)
- [Denormalizers](#denormalizers)
- [Validators](#validators)
- [Events](#events)
- [Kafka Callbacks](#kafka-callbacks)
- [Producing Messages](#producing-messages)
- [Custom configurations](#custom-configurations)
- [Showing current consumer/producer configuration](#showing-current-consumer-producer-configuration)
- [License](#license)

# Technology stack

- PHP >=7.4
- ext-rdkafka for PHP
- symfony components: refer to composer.json `require` section for required package versions

# Quick start

If you wish to install it in your Symfony project:
```
composer require sts-gaming-group/kafka-bundle
```

# Example project

If you want to test out capabilities of this bundle in a Symfony project, please refer to **https://github.com/sts-gaming-group/kafka-bundle-app** project which ships with kafka-bundle and docker-compose file for convenience.   


# Basic Configuration

1. Add **sts_gaming_group_kafka.yaml** to config folder at **config/packages/sts_gaming_group_kafka.yaml** or in a specific env folder i.e. **config/packages/prod/sts_gaming_group_kafka.yaml**
2. Add configuration to sts_gaming_group_kafka.yaml for example:
 ```yaml
sts_gaming_group_kafka:
  consumers:
    instances:
      App\Consumers\ExampleConsumer:
        brokers: [ '127.0.0.1:9092', '127.0.0.2:9092', '127.0.0.3:9092' ]
        schema_registry: 'http://127.0.0.1:8081'
        group_id: 'some_group_id'
        topics: [ 'some_topic' ]
  producers:    
    instances:
      App\Producers\ExampleProducer:
        brokers: [ '127.0.0.1:9092', '127.0.0.2:9092', '127.0.0.3:9092' ]    
        producer_topic: 'my_app_failed_message_topic'
   ```
3. Most of the time you would like to keep your kafka configuration in the yaml file, but you can also pass configuration directly in CLI for example:
```
bin/console kafka:consumers:consume example_consumer --group_id some_other_group_id
```

Currently, options passed in CLI only work for consumers which are run by command `kafka:consumers:consume`.

The configurations are resolved in runtime. The priority is as follows:

- Configurations passed in CLI will always take precedence
- Configurations passed per consumer/producer basis (```instances:``` section in `consumers:` or `producers:` in sts_gaming_group_kafka.yaml)

# Consuming messages
1. Create consumer
```php
<?php

declare(strict_types=1);

namespace App\Consumers;

use StsGamingGroup\KafkaBundle\Client\Consumer\Message;
use StsGamingGroup\KafkaBundle\Client\Contract\ConsumerInterface;
use StsGamingGroup\KafkaBundle\RdKafka\Context;

class ExampleConsumer implements ConsumerInterface
{
    public const CONSUMER_NAME = 'example_consumer';

    public function consume(Message $message, Context $context): void
    {
        $data = $message->getData(); // contains denormalized data from Kafka
        $retryNo = $context->getRetryNo();  // contains retry count in case of a failure
    }

    public function handleException(\Exception $exception, Context $context): void
    {
        // log it or i.e. produce to retry topic based on type of exception
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

# Retrying failed messages

To trigger a backoff retry, your consumer should throw RecoverableMessageException in `consume` method. Also, you have to configure few retry options in sts_gaming_group_kafka.yaml
```php
use StsGamingGroup\KafkaBundle\Client\Consumer\Exception\RecoverableMessageException;
```
```yaml
sts_gaming_group_kafka:
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
`It is important to remember about committing offsets in Kafka in case of a permanently failed message (in case enable_auto_commit is set to false).`

Any uncaught exception in your consumer will shut down the consumer.

# Handling offsets

By default, option `enable.auto.commit` is set true. In such cases after consuming a message, offsets will be committed automatically to Kafka brokers.
Frequency in which offsets are committed is described by option `auto.commit.interval.ms` (defaults to 50ms). It means
that every 50ms Librdkafka (library that manages Kafka underneath PHP process) will send currently stored offset to Kafka Broker.
It also means, that if you consume a message, and your PHP process dies after 49ms the message will not be committed and 
after restarting the consumer you will receive the same message again. Such a situation is very unlikely but may happen.


Kafka guarantess at-least-once-delivery per message - per topic - per consumer group.id. One of implications of such behavior is that if offset is not committed 
to Broker, Kafka will resend you the same message again. It is up to developer to handle such cases.

One approach to be 100% sure about offsets commit is to handle them manually by setting `enable.auto.commit` to false.
You can then use `CommitOffsetTrait::commitOffset()` method to send current offset to Broker.
```php 
<?php

declare(strict_types=1);

namespace App\Consumers;

use StsGamingGroup\KafkaBundle\Client\Traits\CommitOffsetTrait;

class ExampleConsumer implements ConsumerInterface
{
   use CommitOffsetTrait;
   
   public function consume(Message $message, Context $context): void
   {
      // process the message
      $this->commitOffset($context); // manually commits the offset
   }
}
```

Manually committing offsets gives you almost 100% confidence that you will not receive the same message again. There is, however, still small chance that offset will not be saved
to Broker for example when there is a network issue. Again, it is up to a developer to handle such cases (probably with a `try...catch` block while committing offsets).

There is, however, one big downside to manual commits - they are slow. The reason is that commits have to be done inside your 
PHP process and therefore it blocks your main thread. Each commit may last i.e. about 40-50 ms which in case of Kafka is incredibly slow.
You can pass `true` as a second argument to `$this->commitOffset($context, true);` Manual commits will then be handled asynchronously
and will be much faster - but again in case your PHP process dies while committing, some offsets may not be send to Broker (almost the same story when `enable.auto.commit` is set to true and your process dies).

Looking at above situations it is rather recommended to keep `enable.auto.commit` option set to true and handle possible duplicated
messages inside your application.

# Decoders

Decoders are meant to turn raw Kafka data (json, avro, plain text or anything else) into PHP array (or actually any format you'd like). There are three decoders available:
- AvroDecoder
- JsonDecoder (which actually only does json_decode on kafka raw data)
- PlainDecoder (which actually does not decode the message but passes you a raw version of it)


By default, this package uses AvroDecoder and requires a schema_registry configuration. Schema registry should be a URL containing schema versions of consumed messages.


You can also implement your own decoder by implementing `DecoderInterface`
```php
<?php

namespace App\Decoder;

use StsGamingGroup\KafkaBundle\Configuration\ResolvedConfiguration;
use StsGamingGroup\KafkaBundle\Decoder\Contract\DecoderInterface;

class CustomDecoder implements DecoderInterface
{
    public function decode(ResolvedConfiguration $configuration, string $message)
    {
        // $configuration contains values from sts_gaming_group_kafka.yaml or CLI
        // $message contains raw value from Kafka
    }
}
```

Register it in your configuration
```yaml
sts_gaming_group_kafka:
  consumers:
    instances:
      App\Consumers\ExampleConsumer:
        decoder: App\Decoder\CustomDecoder
```

# Denormalizers

You may also want to denormalize the message into some kind of DTO or any other object you wish.
By default, this bundle does not denormalize the message into any object and passes you an array (which comes from the AvroDecoder).

Your denormalizers must implement DenormalizerInterface and requires you to implement `denormalize` method. Return value may be of any kind.
```php
<?php

declare(strict_types=1);

namespace App\Normalizer;

use StsGamingGroup\KafkaBundle\Denormalizer\Contract\DenormalizerInterface;

class CustomDenormalizer implements DenormalizerInterface
{
    public function denormalize($data): MessageDTO
    {
        // $data is an array which comes from AvroDecoder or some other registered Decoder
        $messageDTO = new MessageDTO();
        $messageDTO->setName($data['name']);

        return $messageDTO;
    }
}
```
Register it in your configuration:
```yaml
sts_gaming_group_kafka:
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
    public function consume(Message $message, Context $context): void
    {
        $messageDTO = $message->getData(); // $messageDTO comes from CustomDenormalizer
    }
}
```

# Validators

After of before denormalization, you may want to validate if given object should be passed to your consumer - you may want, for example, to filter out incomplete data that came from Broker.

1. Create validator
```php
<?php

declare(strict_types=1);

namespace App\Validator;

use StsGamingGroup\KafkaBundle\Validator\Contract\ValidatorInterface;
use StsGamingGroup\KafkaBundle\Validator\Validator;

class ExampleValidator implements ValidatorInterface
{
    public function validate($decoded): bool
    {
        return !array_key_exists('foo', $decoded);
    }

    public function failureReason($decoded): string
    {
        return sprintf('Missing foo key in decoded message.');
    }
    
    public function type() : string
    {
       return Validator::PRE_DENORMALIZE_TYPE; // runs before denormalization
       // Validator::POST_DENORMALIZE_TYPE // runs after denormalization
    }
}
```
Register it in your configuration:
```yaml
sts_gaming_group_kafka:
  consumers:
    instances:
      App\Consumers\ExampleConsumer:
        validators: 
         - App\Validator\ExampleValidator
         - App\Validator\SomeOtherValidator      
```
You may have multiple validators attached to one consumer. The priority of called validators is exactly how you defined them in sts_gaming_group_kafka.yaml - 
so in this case ExampleValidator is called first, and then SomeOtherValidator is called.

If a validator returns false, an instance of ValidatorException is thrown. 
```php
 ...
 
 use StsGamingGroup\KafkaBundle\Validator\Exception\ValidationException;
 
 public function handleException(\Exception $exception, Context $context)
 {
     if ($exception instanceof ValidationException) {      
         $decoded = $exception->getData();
         $this->logger->info(
             sprintf(
                 'Message has not passed validation. Id: %s  | Reason: %s', 
                 $decoded['id'],
                 $exception->getFailedReason())
         );
     }
 }
```
`Offset for a message which has not passed validation is committed automatically.`


# Events
Consumer dispatches events using **symfony/event-dispatcher** component as an optional dependency:

Only for currently running consumer:
- **sts_gaming_group_kafka.pre_message_consumed_{consumer_name}** e.g. sts_gaming_group_kafka.pre_message_consumed_example_consumer
- **sts_gaming_group_kafka.post_message_consumed_{consumer_name}** e.g. sts_gaming_group_kafka.post_message_consumed_example_consumer

Global event for all consumers:
- **StsGamingGroup\KafkaBundle\Event\PreMessageConsumedEvent**
- **StsGamingGroup\KafkaBundle\Event\PostMessageConsumedEvent**

As the name suggests - first event is dispatched before the message is consumed, and the second event is dispatched just after the message has been consumed (retry mechanism is not taken into account, message needs to be processed fully for the event to be dispatched).
You can hook up into these events using symfony event subscriber/listener i.e.

```php
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use StsGamingGroup\KafkaBundle\Event\PostMessageConsumedEvent;
use StsGamingGroup\KafkaBundle\Event\PreMessageConsumedEvent;

class ExampleConsumerEventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            PreMessageConsumedEvent::getEventName('example_consumer') => 'onPreMessageConsumed',
            PostMessageConsumedEvent::getEventName('example_consumer') => 'onPostMessageConsumed',
            PreMessageConsumedEvent::class => 'onGlobalPreMessageConsumed',
            PostMessageConsumedEvent::class => 'onGlobalPostMessageConsumed'
        ];
    }

    public function onPreMessageConsumed(PreMessageConsumedEvent $event): void
    {
        $event->getConsumedMessages(); // number of processed messages
        $event->getConsumptionTimeMs(); // how long consumer is running
    }

    public function onPostMessageConsumed(PostMessageConsumedEvent $event): void
    {
        $event->getConsumedMessages();
        $event->getConsumptionTimeMs();
    }
}
```

# Kafka Callbacks

Librdkafka (C/C++ library used underneath PHP) provides several callbacks that you can use in different situations (consuming/producing/error handling/logging). 
Your consumer must implement CallableInterface which requires you to define `callbacks` method. This method should return an array
of callbacks you wish to handle yourself.

```php
<?php

declare(strict_types=1);

namespace App\Consumers;

use StsGamingGroup\KafkaBundle\Client\Contract\CallableInterface;
use StsGamingGroup\KafkaBundle\Client\Contract\ConsumerInterface;
use StsGamingGroup\KafkaBundle\RdKafka\Callbacks;

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

# Producing Messages

1. To produce messages you must configure few options in sts_gaming_group_kafka.yaml:
```yaml 
producers:
 instances:
   App\Producers\ExampleProducer:
     brokers: [ '127.0.0.1:9092', '127.0.0.2:9092', '127.0.0.3:9092' ]
     producer_topic: 'topic_i_want_to_produce_to' #only one topic allowed per producer
```

2. Create data object which you want to work on (i.e. some entity or DTO)
```php 
<?php

declare(strict_types=1);

namespace App\Producers;

class SomeEntity
{
    private int $id;
    private string $name;

    public function __construct(int $id, string $name)
    {
        $this->id = $id;
        $this->name = $name;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name
        ];
    }
}
```
3. Create a producer which will work on your data object and create Message for Kafka
```php
<?php

declare(strict_types=1);

namespace App\Producers;

use StsGamingGroup\KafkaBundle\Client\Contract\ProducerInterface;
use StsGamingGroup\KafkaBundle\Client\Producer\Message;

class ExampleProducer implements ProducerInterface
{
    public function produce($data): Message
    {
        /** @var SomeEntity $data */
        return new Message(json_encode($data->toArray()), $data->getId());
        // first argument of Message is the payload as a string
        // second argument is a message key which is used to help kafka partition messages
    }

    public function supports($data): bool
    {
        // in case of many producers you should check what $data is passed here
        return $data instanceof SomeEntity;
    }
}
```
4. Push message by calling ProducerClient::produce() i.e. somewhere in your Command class
```php
<?php

declare(strict_types=1);

namespace App\Command;

use StsGamingGroup\KafkaBundle\Client\Producer\ProducerClient;

class ExampleCommand extends Command
{ 
 public function __construct(ProducerClient $client, SomeEntityRepository $repository)
 {
     $this->client = $client;
     $this->repository = $repository;
 }
 
 protected function execute(InputInterface $input, OutputInterface $output): int
 {
     $someEntities = $this->repository->findAll();
     foreach ($someEntities as $entity) {
         $this->client->produce($entity);
     }

     $this->client->flush(); // call flush after produce() method has finished

     return Command::SUCCESS;
 }
```
5. To produce message to a specific partition your Producer can implement PartitionAwareProducerInterface
```php
<?php

declare(strict_types=1);

namespace App\Producers;

use StsGamingGroup\KafkaBundle\Client\Contract\PartitionAwareProducerInterface;
use StsGamingGroup\KafkaBundle\Client\Producer\Message;

class ExampleProducer implements ProducerInterface
{
    public function produce($data): Message
    {
        /** @var SomeEntity $data */
        return new Message(json_encode($data->toArray()), $data->getId());
    }

    public function getPartition($data, ResolvedConfiguration $configuration): int
    {
        /** @var SomeEntity $data */
        return $data->getId() % 16; // calculating modulo from object id to produce to maximum of 16 partitions (0-15)
    }
}
```
6. You can also set callbacks array to Producer, for example, to check if messages were sent successfully. Your producer class should implement CallableInterface.
```php
use StsGamingGroup\KafkaBundle\Client\Contract\CallableInterface;
use StsGamingGroup\KafkaBundle\Client\Contract\ProducerInterface;

class ExampleProducer implements ProducerInterface, CallableInterface
{
    public function callbacks(): array
    {
        // callbacks array just like in Consumer example
    }
}
```
7. Other options that can be configured for ProducerClient at runtime:
```php 
$this->producerClient
   ->setPollingBatch(25000)   
   ->setPollingTimeoutMs(1000)
   ->setFlushTimeoutMs(500)
   ->setMaxFlushRetries(10);
```
- polling batch - after how many messages (in case of a loop, as in example above with $someEntities) ProducerClient should call librdkafka `poll` method.
If you produce big messages and do not call poll frequently there might be an issue of librdkafka full internal queue. Also, consumers will not receive anything until `poll` has been called.
  So it is recommended to keep polling batch number at reasonable level i.e. 10000 or 20000
- polling timeout ms - how long librdkafka will wait until polling of a message finishes
- flush timeout ms, max flush retries - after calling `flush()` ProducerClient will try to flush remaining messages in librdkafka internal queue. Remaining messages are those who have not been `poll`ed yet.

# Custom configurations

Some times you may wish to pass some additional options to your Consumer object. You may add your own configuration:
```php
<?php

declare(strict_types=1);

namespace App\Configuration;

use StsGamingGroup\KafkaBundle\Configuration\Contract\ConfigurationInterface;
use Symfony\Component\Console\Input\InputOption;

class Divisor implements ConfigurationInterface
{
    public function getName(): string
    {
        return 'divisor';
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

    public function getDefaultValue(): int
    {
        return 1;
    }
}
```
Custom option may only be passed in CLI
```
bin/console kafka:consumers:consume example_consumer --divisor 4 --remainder 1 --group_id first_group
bin/console kafka:consumers:consume example_consumer --divisor 4 --remainder 2 --group_id second_group
etc.
```
You will receive it in consume method, and you may take actions accordingly.
```php
class ExampleConsumer implements ConsumerInterface
{
    public const CONSUMER_NAME = 'example_consumer';

    public function consume(Message $message, Context $context): void
    {
        $divisor = $context->getValue(Divisor::NAME);
        $remainder = $context->getValue(Remainder::NAME);
        
        if ($message->getId() % $divisor !== $remainder) {
            return; // let's skip that message
        }
        
        // process message normally
    }
}
```

Example above shows how you could scale up your application by executing i.e. 4 consumers/commands with different remainders and group ids. You may have to resort to such tactics if your topic has only one partition and there is no way to scale up your consumer. 

# Showing current consumer/producer configuration

You can show current configuration that will be passed to consumer by calling following command
```
bin/console kafka:consumers:describe example_consumer
┌───────────────────────────┬─────────────────────────────────────────────────────────┐
│ configuration             │ value                                                   │
├───────────────────────────┼─────────────────────────────────────────────────────────┤
│ class                     │ App\Consumers\ExampleConsumer                           │
│ topics                    │ some_topic                                              │
│ group_id                  │ some_group_id                                           │
│ brokers                   │ 127.0.0.1:9092, 127.0.0.2:9092, 127.0.0.3:9092          │
│ offset_store_method       │ broker                                                  │
│ timeout                   │ 1000                                                    │
│ auto_offset_reset         │ smallest                                                │
│ auto_commit_interval_ms   │ 5                                                       │
│ decoder                   │ StsGamingGroup\KafkaBundle\Decoder\AvroDecoder                     │
│ schema_registry           │ http://127.0.0.1:8081                                   │
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
│ brokers            │ 127.0.0.1:9092, 127.0.0.2:9092, 127.0.0.3:9092          │
│ log_level          │ 3                                                       │
│ producer_partition │ -1                                                      │
│ producer_topic     │ topic_i_want_to_produce_to                              │
└────────────────────┴─────────────────────────────────────────────────────────┘
```

# License

This package is distributed under **MIT license**. Please refer to LICENSE.md for more details. 

