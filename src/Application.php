<?php

namespace Sikhlana\Backup;

use Sikhlana\Backup\Commands\Backup;
use Sikhlana\Backup\Commands\CreateKeyFile;
use NunoMaduro\Collision\Provider;
use Sikhlana\Backup\Exceptions\JsonValidationException;
use Symfony\Component\Console\Application as BaseApplication;
use Symfony\Component\Console\Output\OutputInterface;

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

    protected function doRenderException(\Exception $e, OutputInterface $output)
    {
        parent::doRenderException($e, $output);

        if ($e instanceof JsonValidationException) {
            foreach ($e->getErrors() as $error) {
                $output->writeln('<error>  ' . $error . '</error>');
            }
        }
    }
}