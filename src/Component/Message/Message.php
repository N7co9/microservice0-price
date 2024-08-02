<?php
declare(strict_types=1);

namespace App\Component\Message;

class Message
{
    private mixed $content;
    public function __construct(mixed $content)
    {
        $this->content = $content;
    }

    public function getContent(): mixed
    {
        return $this->content;
    }
}