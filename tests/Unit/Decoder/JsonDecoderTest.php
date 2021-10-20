<?php

declare(strict_types=1);

namespace StsGamingGroup\KafkaBundle\Tests\Unit\Decoder;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use StsGamingGroup\KafkaBundle\Configuration\ResolvedConfiguration;
use StsGamingGroup\KafkaBundle\Decoder\JsonDecoder;

class JsonDecoderTest extends TestCase
{
    private MockObject $resolvedConfiguration;

    private JsonDecoder $decoder;

    protected function setUp(): void
    {
        $this->resolvedConfiguration = $this->createMock(ResolvedConfiguration::class);
        $this->decoder = new JsonDecoder();
    }

    public function testDecoded(): void
    {
        $message = '{"status": "ok"}';

        $result = $this->decoder->decode($this->resolvedConfiguration, $message);

        $this->assertEquals(['status' => 'ok'], $result);
    }

    public function testInvalidMessage(): void
    {
        $message = '{"status: "ok"}';

        $this->expectException(\JsonException::class);

        $this->decoder->decode($this->resolvedConfiguration, $message);
    }
}
