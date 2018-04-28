<?php

namespace Sikhlana\Backup\Tasks;

use Symfony\Component\Console\Output\OutputInterface;

abstract class Task
{
    /**
     * @var OutputInterface
     */
    protected $output;

    public function __construct(OutputInterface $output)
    {
        $this->output = $output;
    }

    abstract public function run();
}