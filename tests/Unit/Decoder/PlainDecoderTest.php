<?php

declare(strict_types=1);

namespace Sts\KafkaBundle\Tests\Unit\Decoder;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sts\KafkaBundle\Configuration\ResolvedConfiguration;
use Sts\KafkaBundle\Decoder\PlainDecoder;

class PlainDecoderTest extends TestCase
{
    private MockObject $resolvedConfiguration;

    private PlainDecoder $decoder;

    protected function setUp(): void
    {
        $this->resolvedConfiguration = $this->createMock(ResolvedConfiguration::class);
        $this->decoder = new PlainDecoder();
    }

    public function testTheSameMessage(): void
    {
        $message = 'abc';

        $result = $this->decoder->decode($this->resolvedConfiguration, $message);

        $this->assertEquals('abc', $result);
    }
}
