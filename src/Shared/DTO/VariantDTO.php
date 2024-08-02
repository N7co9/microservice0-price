<?php
declare(strict_types=1);

namespace App\Shared\DTO;

class VariantDTO
{
    public string $id;
    public array $attributes;

    public function __construct(string $id, array $attributes)
    {
        $this->id = $id;
        $this->attributes = $attributes;
    }
}