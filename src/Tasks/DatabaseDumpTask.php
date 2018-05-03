<?php

namespace Sikhlana\Backup\Tasks;

use Defuse\Crypto\File;
use Defuse\Crypto\Key;
use Sikhlana\Backup\Application;
use Spatie\DbDumper\Databases\MongoDb;
use Spatie\DbDumper\Databases\MySql;
use Spatie\DbDumper\Databases\PostgreSql;
use Spatie\DbDumper\Databases\Sqlite;
use Symfony\Component\Console\Output\OutputInterface;

class DatabaseDumpTask extends Task
{
    /**
     * @var Key
     */
    protected $key;

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

    public function __construct(Key $key, string $name, array $config, string $root, OutputInterface $output)
    {
        parent::__construct($output);

        $this->key = $key;
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

        $temp = tempnam(sys_get_temp_dir(), 'gfnbak');
        $dumper->dumpToFile($temp);

        File::encryptFile($temp, sprintf(
            '%s/%s.sql.encrypted', $this->root, date('Y-m-d-H-i-s', Application::$time)
        ), $this->key);

        $this->output->writeln('<info>Successfully dumped the database `' . $this->name . '`.</info>');
        unlink($temp);
    }
}