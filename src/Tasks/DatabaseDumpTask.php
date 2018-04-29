<?php

namespace Sikhlana\Backup\Tasks;

use Spatie\DbDumper\Databases\MongoDb;
use Spatie\DbDumper\Databases\MySql;
use Spatie\DbDumper\Databases\PostgreSql;
use Spatie\DbDumper\Databases\Sqlite;
use Symfony\Component\Console\Output\OutputInterface;

class DatabaseDumpTask extends Task
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var array
     */
    protected $config;

    /**
     * @var string
     */
    protected $root;

    public function __construct(string $name, array $config, string $root, OutputInterface $output)
    {
        parent::__construct($output);

        $this->name = $name;
        $this->root = $root;
        $this->config = $config;
    }

    public function run()
    {
        if (! file_exists($this->root)) {
            mkdir($this->root, 0777, true);
        }

        switch ($this->config['driver']) {
            case 'mysql':
                $dumper = (new MySql)->setHost($this->config['host'])
                                     ->setPort($this->config['port'] ?? 3306)
                                     ->setDbName($this->config['database'])
                                     ->setUserName($this->config['username'])
                                     ->setPassword($this->config['password'] ?? '')
                                     ->setDefaultCharacterSet($this->config['charset'] ?? '')
                                     ->dontSkipComments();
                break;

            case 'pgsql':
                $dumper = (new PostgreSql)->setHost($this->config['host'])
                                          ->setPort($this->config['port'] ?? 5432)
                                          ->setDbName($this->config['database'])
                                          ->setUserName($this->config['username'])
                                          ->setPassword($this->config['password'] ?? '');
                break;

            case 'sqlite':
                $dumper = (new Sqlite)->setDbName($this->config['file']);
                break;

            case 'mongodb':
                $dumper = (new MongoDb)->setHost($this->config['host'])
                                       ->setPort($this->config['port'] ?? 27017)
                                       ->setDbName($this->config['database'])
                                       ->setUserName($this->config['username'] ?? '')
                                       ->setPassword($this->config['password'] ?? '')
                                       ->setCollection($this->config['collection'] ?? '')
                                       ->setAuthenticationDatabase($this->config['authentication-database'] ?? '');
                break;

            default:
                throw new \RuntimeException('Undefined database driver specified.');
        }

        $dumper->dumpToFile(sprintf(
            '%s/%s.sql', $this->root, date('Y-m-d-H-i-s')
        ));

        $this->output->writeln('<info>Successfully dumped the database `' . $this->name . '`.</info>');
    }
}