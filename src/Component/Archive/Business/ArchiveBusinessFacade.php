<?php
declare(strict_types=1);

namespace App\Component\Archive\Business;

use App\Component\Archive\Business\Model\Archive;

class ArchiveBusinessFacade
{
    public function __construct
    (
        public Archive $archive
    )
    {
    }

    public function archiveProcessedData(string $sourcePath, string $archivePath): bool
    {
        return $this->archive->archiveProcessedData($sourcePath, $archivePath);
    }


}