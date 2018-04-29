<?php

namespace Sikhlana\Backup\Commands;

use League\Flysystem\Filesystem;
use Sikhlana\Backup\Concerns\CreatesFilesystem;
use Sikhlana\Backup\Concerns\FetchesHomeDirectory;
use Sikhlana\Backup\Concerns\FetchesKeyFileContents;
use Sikhlana\Backup\Models\Project;
use Sikhlana\Backup\Support\Os;
use Sikhlana\Backup\Tasks\ProjectBackupTask;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class Backup extends Command
{
    use FetchesKeyFileContents, FetchesHomeDirectory, CreatesFilesystem;

    /**
     * @var Filesystem
     */
    private $fs;

    protected function configure()
    {
        $this->setName('backup')
             ->addArgument('source', InputArgument::REQUIRED, 'The absolute path to where the projects\' root directory.')
             ->addOption(
                 'target', 't', InputOption::VALUE_OPTIONAL,
                 'The absolute path where the projects will be backed up.',
                 $this->getHomeDirectory(true) . DIRECTORY_SEPARATOR . 'gfnbackups'
             )
             ->addOption(
                 'dry-run', null, InputOption::VALUE_NONE,
                 'Validates the project json files.'
             )
             ->addOption(
                 'no-databases', null, InputOption::VALUE_NONE,
                 'Use this switch to disable database dumping.'
             )
             ->setDescription('Initiates the backup process.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $source = $input->getArgument('source');

        if (! file_exists($source)) {
            $output->writeln('<error>The source path does not exist.</error>');
            return 1;
        }

        if (! is_dir($source)) {
            $output->writeln('<error>The source path is not a valid directory.</error>');
            return 1;
        }

        if (! is_readable($source)) {
            $output->writeln('<error>The source path is not readable.</error>');
            return 1;
        }

        $this->fs = $this->createLocalFilesystem($source);

        foreach ($this->scanForProjectRoot() as $path) {
            $task = new ProjectBackupTask(
                $this->key, $source . DIRECTORY_SEPARATOR . $path,
                $input->getOption('target'), $output
            );

            if (! $input->getOption('dry-run')) {
                if ($input->getOption('no-databases')) {
                    $task->doNotDumpDatabases();
                }

                $task->run();
            }
        }

        return 0;
    }

    private function scanForProjectRoot($path = '')
    {
        foreach ($this->fs->listContents($path) as $node) {
            if (Os::isSystemPath($node['basename'])) {
                continue;
            }

            if ($node['basename'] == Project::PROJECT_JSON_FILENAME) {
                yield $path;
            } else if ($node['type'] == 'dir') {
                yield from $this->scanForProjectRoot($node['path']);
            }
        }
    }
}