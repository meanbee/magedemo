<?php
namespace Meanbee\MageDemo\Command;

use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class DumpCommand extends TargetCommand {

    protected function configure() {
        parent::configure();

        $this
            ->setName('dump')
            ->setDescription('Create a database backup for one or more configured targets.')
            ->addOption("--force", "-f", InputOption::VALUE_NONE, "If a backup already exists, overwrite it.");
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $this->initBackupDir($this->config->getBackupDir());

        foreach ($this->targets as $target) {
            if (!$output->isQuiet()) {
                $output->writeln(sprintf("Dumping '%s'...", $target));
            }

            $target_data = $this->config->getTarget($target);
            $filename = $this->config->getBackupFilename($target);

            if (!is_dir($target_data['install_dir'])) {
                throw new \Exception(sprintf("Target '%s' is not installed.", $target));
            }
            chdir($target_data['install_dir']);

            if (file_exists($filename) && !$input->getOption('force')) {
                throw new \Exception(sprintf("Backup file '%s' already exists. Use --force to overwrite it.", $filename));
            }

            try {
                /** @var \N98\Magento\Command\Database\DumpCommand $dump */
                $dump = $this->getMagerun()->find("db:dump");
            } catch (\InvalidArgumentException $e) {
                throw new \Exception("'magerun db:dump' command not found. Missing dependencies?");
            }

            $dump_input = new ArrayInput(array(
                'command'       => "db:dump",
                '--compression' => "gzip",
                'filename'      => $filename
            ));

            $dump->run($dump_input, $output);
        }
    }

    /**
     * Prepare the given directory for storing target backups (i.e. create it).
     *
     * @param $dir
     *
     * @throws \Exception
     */
    protected function initBackupDir($dir) {
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        if (!is_writable($dir)) {
            throw new \Exception(sprintf("Backup directory '%s' is not writable.", $dir));
        }
    }
}
