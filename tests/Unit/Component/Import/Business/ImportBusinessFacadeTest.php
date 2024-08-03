<?php
declare(strict_types=1);

namespace App\Tests\Unit\Component\Import\Business;

use App\Component\Import\Business\ImportBusinessFacade;
use App\Component\Import\Business\Model\Import;
use App\Shared\DTO\ArticleDTO;
use App\Shared\DTO\ProductDTO;
use App\Shared\DTO\VariantDTO;
use PHPUnit\Framework\TestCase;

class ImportBusinessFacadeTest extends TestCase
{
    private ImportBusinessFacade $importBusinessFacade;
    private Import $importMock;

    protected function setUp(): void
    {
        $this->importMock = $this->createMock(Import::class);
        $this->importBusinessFacade = new ImportBusinessFacade($this->importMock);
    }

    public function testImport(): void
    {
        $filePath = '/path/to/test/file.xml';
        $expectedResult = [
            new ProductDTO('product1', ['name' => 'Product 1'], [
                new ArticleDTO('article1', ['color' => 'Red'], [], [
                    new VariantDTO('variant1', ['size' => 'M'])
                ])
            ])
        ];

        $this->importMock->expects($this->once())
            ->method('parse')
            ->with($filePath)
            ->willReturn($expectedResult);

        $result = $this->importBusinessFacade->import($filePath);

        $this->assertSame($expectedResult, $result);
        $this->assertContainsOnlyInstancesOf(ProductDTO::class, $result);
        $this->assertCount(1, $result);

        $product = $result[0];
        $this->assertInstanceOf(ProductDTO::class, $product);
        $this->assertEquals('product1', $product->product_id);
        $this->assertEquals(['name' => 'Product 1'], $product->attributes);

        $this->assertCount(1, $product->articles);
        $article = $product->articles[0];
        $this->assertInstanceOf(ArticleDTO::class, $article);
        $this->assertEquals('article1', $article->id);
        $this->assertEquals(['color' => 'Red'], $article->attributes);

        $this->assertCount(1, $article->variants);
        $variant = $article->variants[0];
        $this->assertInstanceOf(VariantDTO::class, $variant);
        $this->assertEquals('variant1', $variant->id);
        $this->assertEquals(['size' => 'M'], $variant->attributes);
    }

    public function testImportWithEmptyResult(): void
    {
        $filePath = '/path/to/empty/file.xml';

        $this->importMock->expects($this->once())
            ->method('parse')
            ->with($filePath)
            ->willReturn([]);

        $result = $this->importBusinessFacade->import($filePath);

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    public function testImportWithInvalidFilePath(): void
    {
        $invalidFilePath = '/path/to/nonexistent/file.xml';

        $this->importMock->expects($this->once())
            ->method('parse')
            ->with($invalidFilePath)
            ->willThrowException(new \InvalidArgumentException('File not found'));

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('File not found');

        $this->importBusinessFacade->import($invalidFilePath);
    }
}