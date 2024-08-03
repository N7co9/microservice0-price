<?php
declare(strict_types=1);

namespace App\Tests\Unit\Component\Message;

use App\Component\Message\Message;
use PHPUnit\Framework\TestCase;
use stdClass;

class MessageTest extends TestCase
{
    /**
     * @dataProvider contentProvider
     */
    public function testGetContentReturnsCorrectValue(mixed $content): void
    {
        $message = new Message($content);
        $this->assertSame($content, $message->getContent());
    }

    public function contentProvider(): array
    {
        return [
            'string' => ['Hello, World!'],
            'integer' => [42],
            'float' => [3.14],
            'boolean' => [true],
            'null' => [null],
            'array' => [[1, 2, 3]],
            'object' => [new stdClass()],
        ];
    }

    public function testSerializationAndDeserialization(): void
    {
        $originalContent = ['key' => 'value'];
        $originalMessage = new Message($originalContent);

        $serialized = serialize($originalMessage);
        $deserializedMessage = unserialize($serialized);

        $this->assertEquals($originalMessage, $deserializedMessage);
        $this->assertEquals($originalContent, $deserializedMessage->getContent());
    }

    public function testMessageCloning(): void
    {
        $originalContent = new stdClass();
        $originalContent->property = 'value';

        $originalMessage = new Message($originalContent);
        $clonedMessage = clone $originalMessage;

        $this->assertNotSame($originalMessage, $clonedMessage);
        $this->assertSame($originalMessage->getContent(), $clonedMessage->getContent());

        $originalContent->property = 'modified';
        $this->assertSame('modified', $clonedMessage->getContent()->property);
    }

    public function testContentTypeConsistency(): void
    {
        $content = 42;
        $message = new Message($content);

        $this->assertIsInt($message->getContent());
    }

    public function testLargeContentHandling(): void
    {
        $largeContent = str_repeat('a', 1000000); // 1MB string
        $message = new Message($largeContent);

        $this->assertEquals($largeContent, $message->getContent());
        $this->assertEquals(1000000, strlen($message->getContent()));
    }
}