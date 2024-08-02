<?php
declare(strict_types=1);

namespace App\Component\Import\Business\Model;

use App\Shared\DTO\ArticleDTO;
use App\Shared\DTO\ProductDTO;
use App\Shared\DTO\VariantDTO;
use DOMDocument;
use DOMXPath;

class Import
{
    public function __construct()
    {
    }

    public function parse(string $filePath): array
    {
        $doc = new DOMDocument();
        $doc->load($filePath);
        $xpath = new DOMXPath($doc);

        $productDTOs = [];
        $products = $xpath->query('//product');

        foreach ($products as $product) {
            $masterId = $product->getAttribute('id');
            $productAttributes = $this->getAttributes($xpath, $product);

            $articleDTOs = [];
            foreach ($xpath->query('./article', $product) as $article) {
                $articleId = $article->getAttribute('id');
                $articleAttributes = $this->getAttributes($xpath, $article);

                $articlePlacements = [];
                foreach ($xpath->query('./articlePlacement', $article) as $placement) {
                    $placementAttributes = [];
                    foreach ($xpath->query('./attribute', $placement) as $placementAttribute) {
                        $identificator = $placementAttribute->getAttribute('identificator');
                        $country = $placementAttribute->getAttribute('country');
                        $key = !empty($country) ? "{$identificator}_{$country}" : $identificator;
                        $placementAttributes[$key] = $placementAttribute->nodeValue;
                    }
                    $articlePlacements[] = $placementAttributes;
                }

                $variantDTOs = [];
                foreach ($xpath->query('./variant', $article) as $variant) {
                    $variantId = $variant->getAttribute('id');
                    $variantAttributes = $this->getAttributes($xpath, $variant);
                    $variantDTOs[] = new VariantDTO($variantId, $variantAttributes);
                }

                $articleDTOs[] = new ArticleDTO($articleId, $articleAttributes, $articlePlacements, $variantDTOs);
            }

            $productDTOs[] = new ProductDTO($masterId, $productAttributes, $articleDTOs);
        }

        return $productDTOs;
    }

    private function getAttributes(DOMXPath $xpath, $node): array
    {
        $attributes = [];
        foreach ($xpath->query("./attribute", $node) as $attribute) {
            $identificator = $attribute->getAttribute('identificator');
            $value = $attribute->nodeValue;

            $country = $attribute->getAttribute('country');
            if (!empty($country)) {
                $identificator = "{$identificator}_{$country}";
            }

            if ($identificator === 'articleFeature') {
                $featureType = $attribute->getAttribute('articleFeatureType');
                $featureId = $attribute->getAttribute('articleFeatureId');
                $key = !empty($featureType) && !empty($featureId) ? "{$identificator}-{$featureType}-{$featureId}" : $identificator;
            } else {
                $key = $identificator;
            }

            $attributes[$key] = $value;
        }
        return $attributes;
    }
}