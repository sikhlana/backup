<?php

namespace Sikhlana\Backup\Tasks;

use Sikhlana\Backup\Concerns\CreatesFilesystem;
use Sikhlana\Backup\Concerns\ParsesProjectJson;
use Sikhlana\Backup\Models\Project;
use League\Flysystem\Filesystem;
use Spatie\DbDumper\Databases\MongoDb;
use Spatie\DbDumper\Databases\MySql;
use Spatie\DbDumper\Databases\PostgreSql;
use Spatie\DbDumper\Databases\Sqlite;
use Symfony\Component\Console\Output\OutputInterface;

class ProjectBackupTask extends Task
{
    use CreatesFilesystem, ParsesProjectJson;

    /**
     * @var string
     */
    protected $source;

    /**
     * @var string
     */
    protected $target;

    /**
     * @var Project
     */
    protected $project;

    /**
     * @var bool
     */
    protected $dumpDatabases = true;

    /**
     * @var string
     */
    protected $key;

    public function __construct(string $key, string $source, string $target, OutputInterface $output)
    {
        parent::__construct($output);

        $this->key = $key;
        $this->source = $source;
        $this->target = $target;

        $this->project = $this->parseProjectJson(
            $source . DIRECTORY_SEPARATOR . Project::PROJECT_JSON_FILENAME
        );
    }

    public function doNotDumpDatabases() {
        $this->dumpDatabases = false;
    }

    public function run()
    {
        $this->project->setSourceDirectory($this->source);
        $this->project->setTargetDirectory($this->target);

        if ($this->dumpDatabases) {
            foreach ($this->getDatabaseDumperInstances() as $name => $dumper) {
                (new DatabaseDumpTask($name, $this->project->getPathForDatabaseDump($name), $dumper, $this->output))->run();
                (new FileEncryptTask($this->key, $this->project->getPathForDatabaseDump($name), $this->output))->run();
            }
        }
    }

    protected function getDatabaseDumperInstances()
    {
        $instances = [];

        foreach ((array) $this->project->getAttribute('databases') as $config) {
            $name = $config['name'];
            $conn = $config['connection'];

            switch ($conn['driver']) {
                case 'mysql':
                    $instances[$name] = (new MySql)->setHost($conn['host'])
                                                   ->setPort($conn['port'] ?? 3306)
                                                   ->setDbName($conn['database'])
                                                   ->setUserName($conn['username'])
                                                   ->setPassword($conn['password'] ?? '')
                                                   ->setDefaultCharacterSet($conn['charset'] ?? '')
                                                   ->dontSkipComments();
                    break;

                case 'pgsql':
                    $instances[$name] = (new PostgreSql)->setHost($conn['host'])
                                                        ->setPort($conn['port'] ?? 5432)
                                                        ->setDbName($conn['database'])
                                                        ->setUserName($conn['username'])
                                                        ->setPassword($conn['password'] ?? '');
                    break;

                case 'sqlite':
                    $instances[$name] = (new Sqlite)->setDbName($conn['file']);
                    break;

                case 'mongodb':
                    $instances[$name] = (new MongoDb)->setHost($conn['host'])
                                                     ->setPort($conn['port'] ?? 27017)
                                                     ->setDbName($conn['database'])
                                                     ->setUserName($conn['username'] ?? '')
                                                     ->setPassword($conn['password'] ?? '')
                                                     ->setCollection($conn['collection'] ?? '')
                                                     ->setAuthenticationDatabase($conn['authentication-database'] ?? '');
                    break;
            }
        }

        return $instances;
    }
}