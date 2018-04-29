<?php

namespace Sikhlana\Backup\Tasks;

use Spatie\DbDumper\DbDumper;
use Symfony\Component\Console\Output\OutputInterface;

class DatabaseDumpTask extends Task
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var DbDumper
     */
    protected $dumper;

    /**
     * @var string
     */
    protected $root;

    public function __construct(string $name, string $root, DbDumper $dumper, OutputInterface $output)
    {
        parent::__construct($output);

        $this->name = $name;
        $this->root = $root;
        $this->dumper = $dumper;
    }

    public function run()
    {
        if (! file_exists($this->root)) {
            mkdir($this->root, 0777, true);
        }

        $this->dumper->dumpToFile(sprintf(
            '%s/%s.sql', $this->root, date('Y-m-d-H-i-s')
        ));

        $this->output->writeln('<info>Successfully dumped the database `' . $this->name . '`.</info>');
    }
}