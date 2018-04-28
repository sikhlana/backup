<?php

namespace Sikhlana\Backup\Concerns;

use Sikhlana\Backup\Exceptions\KeyFileException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

trait FetchesKeyFileContents
{
    /**
     * @var string
     */
    protected $key;

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $filename = $this->getHomeDirectory() . DIRECTORY_SEPARATOR . '.gfnbackupkey';

        if (! file_exists($filename)) {
            throw new KeyFileException('Key file does not exist. Please create a key file first by running the `create-key-file` command.');
        }

        if (! is_readable($filename)) {
            throw new KeyFileException('Will not be able to read the key file.');
        }

        $this->key = file_get_contents($filename);

        if (empty ($this->key)) {
            throw new KeyFileException('The key cannot be empty.');
        }
    }
}