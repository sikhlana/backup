<?php

namespace Sikhlana\Backup\Tasks;

use Sikhlana\Backup\Concerns\CreatesFilesystem;
use Sikhlana\Backup\Concerns\ParsesProjectJson;
use Sikhlana\Backup\Models\Project;
use League\Flysystem\Filesystem;
use Symfony\Component\Console\Output\OutputInterface;

class ProjectBackupTask extends Task
{
    use CreatesFilesystem, ParsesProjectJson;

    /**
     * @var string
     */
    protected $root;

    /**
     * @var Filesystem
     */
    protected $fs;

    /**
     * @var Project
     */
    protected $project;

    public function __construct(string $root, OutputInterface $output)
    {
        parent::__construct($output);

        $this->root = $root;
        $this->fs = $this->createLocalFilesystem($root);
        $this->project = $this->parseProjectJson($root . DIRECTORY_SEPARATOR . Project::PROJECT_JSON_FILENAME);
    }

    public function run()
    {

    }
}