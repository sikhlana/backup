<?php

namespace Sikhlana\Backup\Tasks;

use Symfony\Component\Console\Output\OutputInterface;

class FileEncryptTask extends Task
{
    /**
     * @var string
     */
    protected $key;

    /**
     * @var string
     */
    protected $filename;

    public function __construct(string $key, string $filename, OutputInterface $output)
    {
        parent::__construct($output);

        $this->key = $key;
        $this->filename = $filename;
    }

    public function run()
    {
        // todo.
    }
}