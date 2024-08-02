<?php
declare(strict_types=1);

namespace App\Shared\DTO;

/*
 * A Microservice will send the data imported from the XML, in the same schematic structure of the XML.
 * This means there will be a significant difference between these DTOs and the structure of our main DB in the backend.
 * Therefor after sending these messages, containing the raw XML - packed into DTOs, some kind of mapping has to be done in the backend.
 */

class ProductDTO
{
    public string $product_id;
    public array $attributes;
    public array $articles;

    public function __construct(string $id, array $attributes, array $articles)
    {
        $this->product_id = $id;
        $this->attributes = $attributes;
        $this->articles = $articles;
    }
}