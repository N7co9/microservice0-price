<?php
declare(strict_types=1);

namespace App\Component\Dispatch\Business\Model;

use App\Component\Archive\Business\ArchiveBusinessFacade;
use App\Component\Import\Business\ImportBusinessFacade;
use App\Component\Message\Message;
use Symfony\Component\Messenger\Bridge\Amqp\Transport\AmqpStamp;
use Symfony\Component\Messenger\MessageBusInterface;

class Dispatch
{
    public const routingKey = 'price_key';
    private string $archiveDirectory;

    public function __construct(
        private readonly MessageBusInterface   $bus,
        private readonly ImportBusinessFacade  $import,
        private readonly ArchiveBusinessFacade $archive
    )
    {
        $this->archiveDirectory = $_ENV['ARCHIVE_PATH'];
    }

    public function dispatch(array $fileLocations): void
    {
        foreach ($fileLocations as $singleXmlFile) {
            foreach ($this->import->import($singleXmlFile) as $singleProductDTO) {
                $message = new Message($singleProductDTO);
                $stamp = new AmqpStamp(self::routingKey);
                $this->bus->dispatch($message, [$stamp]);
            }
            $archivePath = $this->archiveDirectory . '/' . basename($singleXmlFile);
            $this->archive->archiveProcessedData($singleXmlFile, $archivePath);
        }
    }
}