<?php

declare(strict_types=1);

namespace Sts\KafkaBundle\Tests\Unit\Factory;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sts\KafkaBundle\Configuration\ResolvedConfiguration;
use Sts\KafkaBundle\Configuration\Type\Decoder;
use Sts\KafkaBundle\Decoder\Contract\DecoderInterface;
use Sts\KafkaBundle\Factory\DecoderFactory;

class DecoderFactoryTest extends TestCase
{
    private MockObject $decoderOne;
    private MockObject $decoderTwo;
    private MockObject $resolvedConfiguration;
    private DecoderFactory $decoderFactory;

    protected function setUp(): void
    {
        $this->decoderOne = $this->createMock(DecoderInterface::class);
        $this->decoderTwo = $this->createMock(DecoderInterface::class);
        $this->resolvedConfiguration = $this->createMock(ResolvedConfiguration::class);
        $this->decoderFactory = new DecoderFactory();
    }

    public function testAddAndCreateDecoder(): void
    {
        $decoderOneClass = get_class($this->decoderOne);
        $this->decoderFactory->addDecoder($this->decoderOne)
            ->addDecoder($this->decoderTwo);

        $this->resolvedConfiguration->expects($this->once())
            ->method('getConfigurationValue')
            ->with(Decoder::NAME)
            ->willReturn($decoderOneClass);

        $this->assertInstanceOf(
            $decoderOneClass,
            $this->decoderFactory->create($this->resolvedConfiguration)
        );
    }
}
