
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

4. Add repository to your project
    - composer config repositories.gitlab.sts.pl/26 '{"type": "composer", "url":"https://gitlab.sts.pl/api/v4/group/26/-/packages/composer/packages.json"}'

5. Install package with desired version
    - composer req sts/kafka-bundle:\<version>

## Usage

1. Add sts_kafka.yaml to config folder at \<root_folder>/config/packages/sts_kafka.yaml or in a specific env folder i.e. \<root_folder>/config/packages/prod/sts_kafka.yaml
2. Add configuration to sts_kafka.yaml for example:
	```yaml
	sts_kafka:
	  brokers: ['172.25.0.201:9092']
	  topics: ['testing.dwh_kafka.tab_tickets_live']
	  group_id: kafka-bundle-test
	  schema_registry: http://172.25.0.201:8081
	  ```
3. Create consumer
```php
<?php

declare(strict_types=1);

namespace App\Consumers;

use Sts\KafkaBundle\Consumer\Contract\ConsumerInterface;
use Sts\KafkaBundle\RdKafka\Context;
use Sts\KafkaBundle\Consumer\Message;

class ExampleConsumer implements ConsumerInterface
{
	public const CONSUMER_NAME = 'example_consumer';

    public function consume(Message $message, Context $context): bool
    {
	  // $context contains resolved configuration, rd kafka consumer object and topics
	  // $message->getDecodedPayload() contains the actual Kafka payload

	  return true;
    }

    public function getName(): string
    {
	  return self::CONSUMER_NAME;
    }
 }
 ```
 4. Run consumer
 ```
 bin/console kafka:consumers:consume example_consumer
 ```
The Message object should contain Kafka payload if configuration was done properly.

### Configuration
Most of the time you will like to keep your kafka configuration in sts_kafka.yaml but you can also pass configuration directly in CLI for example:
```
bin/console kafka:consumers:consume example_consumer --group_id some_other_group_id
```
This allows you to scale one Consumer object with different configurations.
If you have many consumers in one application you can also define configuration per consumer basis in sts_kafka.yaml - for example:
```yaml
	sts_kafka:
	  brokers: ['172.25.0.201:9092']
	  schema_registry: http://172.26.0.201:8081
	  consumers:
		  App\ExampleConsumer:
			  topics: ['testing.dwh_kafka.tab_tickets_live']
			  group_id: group_1
		  App\ExampleConsumerTwo:
			  topics: ['testing.dwh_kafka.tab_tickets_prematch']
			  group_id: group_2
```

The configurations are resolved in runtime. The priority is as follows:

- Configurations passed in CLI will always take precedence
- Configurations passed per consumer basis (```consumers:``` section in sts_kafka.yaml) take precedence over global configuration in sts_kafka.yaml

### Decoders

By default this package uses AvroDecoder which decodes messages from AVRO format into PHP array. It uses schema_registry option to find proper URL containing schema versions. You can change this implementation by defining your own decoder
```php
<?php

declare(strict_types=1);

namespace App\Decoder;

use Sts\KafkaBundle\Configuration\ResolvedConfiguration;
use Sts\KafkaBundle\Decoder\Contract\DecoderInterface;
use App\Decoder\MyCustomDecodedMessage;

class JsonDecoder implements DecoderInterface
{
    public function decode(ResolvedConfiguration $configuration, string $message): MyCustomDecodedMessage
    {
	    // $message contains original kafka payload
		// Message->getDecodedPayload() will contain whatever you return here - there is no defined return type hint on this method
    }
}
```

And register it in your configuration
```yaml
sts_kafka:
	decoder: App\Decoder\JsonDecoder
	...
```
or in CLI
```
bin/console kafka:consumers:consume example_consumer --decoder App\\Decoder\\JsonDecoder
```

### Custom configurations

Some times you may wish to pass some additional options to your Consumer object. You may add your own configuration:
```php
<?php

declare(strict_types=1);

namespace App\Configuration;

use Sts\KafkaBundle\Configuration\Contract\ConfigurationInterface;
use Symfony\Component\Console\Input\InputOption;

class Modulo implements ConfigurationInterface
{
	public const NAME = 'modulo';

	public function getName(): string
    {
	   return self::NAME;
    }

	public function getMode(): int
    {
	   return InputOption::VALUE_REQUIRED;
    }

    public function getDescription(): string
    {
	  return 'My awesome modulo configuration';
	}

    public function getDefaultValue(): int
    {
	  return 0;
    }
 }
```
It will then be available either in sts_kafka.yaml or in CLI
```
bin/console kafka:consumers:consume example_consumer --modulo 4
```
You will receive it in consume method and you may take actions accordingly.
```php
public function consume(Message $message, Context $context): bool
{
  $modulo = $context->getConfigurationValue(Modulo::NAME); // 4

  return true;
}
```

### Debug

You can show current configuration that will be passed to Consumer by adding --describe to command
```
bin/console kafka:consumers:consume example_consumer --describe
```

# To be continued...
