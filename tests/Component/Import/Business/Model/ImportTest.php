<?php
declare(strict_types=1);

namespace Tests\Component\Import\Business\Model;

use App\Component\Import\Business\Model\Import;
use App\Shared\DTO\ArticleDTO;
use App\Shared\DTO\ProductDTO;
use App\Shared\DTO\VariantDTO;
use PHPUnit\Framework\TestCase;

class ImportTest extends TestCase
{
    private Import $import;
    private string $testFilePath;

    protected function setUp(): void
    {
        $this->import = new Import();
        $this->testFilePath = sys_get_temp_dir() . '/test_import_' . uniqid() . '.xml';
    }

    public function testParse(): void
    {
        $xml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<products>
    <product id="product1">
        <attribute identificator="name">Product 1</attribute>
        <article id="article1">
            <attribute identificator="color">Red</attribute>
            <articlePlacement>
                <attribute identificator="stock" country="US">100</attribute>
            </articlePlacement>
            <variant id="variant1">
                <attribute identificator="size">M</attribute>
            </variant>
        </article>
    </product>
</products>
XML;

        file_put_contents($this->testFilePath, $xml);

        $result = $this->import->parse($this->testFilePath);

        $this->assertCount(1, $result);
        $this->assertInstanceOf(ProductDTO::class, $result[0]);

        $product = $result[0];
        $this->assertEquals('product1', $product->product_id);
        $this->assertEquals(['name' => 'Product 1'], $product->attributes);

        $this->assertCount(1, $product->articles);
        $article = $product->articles[0];
        $this->assertInstanceOf(ArticleDTO::class, $article);
        $this->assertEquals('article1', $article->id);
        $this->assertEquals(['color' => 'Red'], $article->attributes);

        $this->assertCount(1, $article->articlePlacement);
        $this->assertEquals(['stock_US' => '100'], $article->articlePlacement[0]);

        $this->assertCount(1, $article->variants);
        $variant = $article->variants[0];
        $this->assertInstanceOf(VariantDTO::class, $variant);
        $this->assertEquals('variant1', $variant->id);
        $this->assertEquals(['size' => 'M'], $variant->attributes);
    }

    public function testParseWithArticleFeature(): void
    {
        $xml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<products>
    <product id="product2">
        <article id="article2">
            <attribute identificator="articleFeature" articleFeatureType="color" articleFeatureId="1">Blue</attribute>
        </article>
    </product>
</products>
XML;

        file_put_contents($this->testFilePath, $xml);

        $result = $this->import->parse($this->testFilePath);

        $this->assertCount(1, $result);
        $product = $result[0];
        $article = $product->articles[0];
        $this->assertEquals(['articleFeature-color-1' => 'Blue'], $article->attributes);
    }

    public function testParseEmptyFile(): void
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?><products></products>';
        file_put_contents($this->testFilePath, $xml);

        $result = $this->import->parse($this->testFilePath);

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }


    protected function tearDown(): void
    {
        if (file_exists($this->testFilePath)) {
            unlink($this->testFilePath);
        }
    }
}