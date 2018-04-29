<?php

namespace Sikhlana\Backup\Tasks;

use Defuse\Crypto\Key;
use Sikhlana\Backup\Concerns\CreatesFilesystem;
use Sikhlana\Backup\Concerns\ParsesProjectJson;
use Sikhlana\Backup\Models\Project;
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
     * @var Key
     */
    protected $key;

    public function __construct(Key $key, string $source, string $target, OutputInterface $output)
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
            foreach ($this->project->getAttribute('databases') as $config) {
                (new DatabaseDumpTask($this->key, $config['name'], $config['connection'], $this->project->getPathForDatabaseDump($config['name']), $this->output))->run();
            }
        }
    }
}