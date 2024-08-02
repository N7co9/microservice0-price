<?php
declare(strict_types=1);

namespace App\Component\Dispatch\Business;

use App\Component\Dispatch\Business\Model\Dispatch;

class DispatchBusinessFacade
{
    public function __construct
    (
        public Dispatch $dispatch
    )
    {
    }

    public function dispatch(array $fileLocations): void
    {
        $this->dispatch->dispatch($fileLocations);
    }

}