<?php
declare(strict_types=1);

namespace App\Shared\DTO;

class ArticleDTO
{
    public string $id;
    public array $attributes;

    public array $articlePlacement;
    public array $variants;

    public function __construct(?string $id, ?array $attributes, ?array $articlePlacement, ?array $variants)
    {
        $this->id = $id;
        $this->attributes = $attributes;
        $this->articlePlacement = $articlePlacement;
        $this->variants = $variants;
    }
}