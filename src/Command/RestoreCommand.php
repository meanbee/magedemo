<?php
namespace Meanbee\MageDemo\Command;

use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RestoreCommand extends TargetCommand {

    protected function configure() {
        parent::configure();

        $this
            ->setName('restore')
            ->setDescription('Restore one or more configured targets from their backups.');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        foreach ($this->targets as $target) {
            if (!$output->isQuiet()) {
                $output->writeln(sprintf("Restoring '%s'...", $target));
            }

            $target_data = $this->config->getTarget($target);
            $backup_file = $this->config->getBackupFilename($target);

            if (!is_dir($target_data['install_dir'])) {
                throw new \Exception(sprintf("Target '%s' is not installed.", $target));
            }
            chdir($target_data['install_dir']);

            if (!file_exists($backup_file)) {
                throw new \Exception(sprintf("No backup found for target '%s'", $target));
            }

            try {
                /** @var \N98\Magento\Command\Database\ImportCommand $import */
                $import = $this->getMagerun()->find("db:import");
            } catch (\InvalidArgumentException $e) {
                throw new \Exception("'magerun db:import' command not found. Missing dependencies?");
            }

            $import_input = new ArrayInput(array(
                'command'       => 'db:import',
                '--compression' => 'gzip',
                'filename'      => $backup_file
            ));

            $import->run($import_input, $output);
        }
    }
}
