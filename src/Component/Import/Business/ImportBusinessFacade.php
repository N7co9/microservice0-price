<?php
declare(strict_types=1);

namespace App\Component\Import\Business;

use App\Component\Import\Business\Model\Import;

readonly class ImportBusinessFacade
{
    public function __construct
    (
        private Import $import
    )
    {
    }

    public function import(string $filePath): array
    {
        return $this->import->parse($filePath);
    }

}