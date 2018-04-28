<?php

namespace Sikhlana\Backup\Commands;

use Sikhlana\Backup\Concerns\FetchesHomeDirectory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CreateKeyFile extends Command
{
    use FetchesHomeDirectory;

    protected function configure()
    {
        $this->setName('create-key-file')
             ->addOption(
                 'force', 'f', InputOption::VALUE_NONE,
                 'Overwrites an existing key file. [WARNING: Will not be able to decrypt backup files that use the old key file.]'
             )
             ->setDescription('Creates a key file in user\'s home directory that will be used for encryption.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $filename = $this->getHomeDirectory(true) . DIRECTORY_SEPARATOR . '.gfnbackupkey';

        if (file_exists($filename) && ! $input->getOption('force')) {
            $output->writeln('<error>An existing key file already exists!</error>');
            $output->writeln('<comment>Use the `-f` switch to force an overwrite (not recommended).</comment>');
            return 1;
        }

        file_put_contents($filename, random_bytes(256));
        $output->writeln('<info>Key file successfully created.</info>');

        return 0;
    }
}