<?php

namespace App\Commands;

use App\Concerns\CreatesFilesystem;
use App\Concerns\FetchesHomeDirectory;
use App\Concerns\FetchesKeyFileContents;
use App\Models\Project;
use App\Support\Os;
use App\Tasks\ProjectBackupTask;
use League\Flysystem\Filesystem;
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
            $task = new ProjectBackupTask($source . DIRECTORY_SEPARATOR . $path, $output);

            if (! $input->getOption('dry-run')) {
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