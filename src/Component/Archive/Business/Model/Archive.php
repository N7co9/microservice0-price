<?php
declare(strict_types=1);

namespace App\Component\Archive\Business\Model;

use RuntimeException;

class Archive
{
    public function archiveProcessedData(string $sourcePath, string $archivePath): bool
    {
        $this->validateSourcePath($sourcePath);
        $this->ensureDirectoryExists(dirname($archivePath));
        $this->moveFile($sourcePath, $archivePath);

        return true;
    }

    private function validateSourcePath(string $sourcePath): void
    {
        if (!file_exists($sourcePath)) {
            throw new RuntimeException("The source file does not exist.");
        }
    }

    private function ensureDirectoryExists(string $directoryPath): void
    {
        if (!is_dir($directoryPath) && !mkdir($directoryPath, 0777, true) && !is_dir($directoryPath)) {
            throw new RuntimeException("Failed to create the archive directory.");
        }
    }

    private function moveFile(string $sourcePath, string $archivePath): void
    {
        if (!rename($sourcePath, $archivePath)) {
            throw new RuntimeException("There was a problem in moving the file to the archive.");
        }
    }
}
