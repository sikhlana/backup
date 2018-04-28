<?php

namespace App;

use Sikhlana\Backup\Commands\Backup;
use Sikhlana\Backup\Commands\CreateKeyFile;
use NunoMaduro\Collision\Provider;
use Symfony\Component\Console\Application as BaseApplication;

class Application extends BaseApplication
{
    const VERSION = '1.0.0 Alpha 1';

    public function __construct()
    {
        parent::__construct('GoodForNothing Backup', self::VERSION);

        (new Provider)->register();
        $this->setCommands();
    }

    private function setCommands()
    {
        $this->addCommands([
            new CreateKeyFile,
            new Backup,
        ]);
    }
}