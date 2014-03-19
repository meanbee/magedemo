<?php
namespace Meanbee\MageDemo\Command;

use Meanbee\MageDemo\Modman;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class InstallCommand extends TargetCommand {

    /**
     * Use Magerun to run a Magento installation for the specified target.
     *
     * @param array $data Target data.
     *
     * @throws \Exception
     */
    protected function installMagento($data) {
        try {
            /** @var \N98\Magento\Command\Installer\InstallCommand $installer */
            $installer = $this->getMagerun()->find("install");
        } catch (\InvalidArgumentException $e) {
            throw new \Exception("'magerun install' command not found. Missing dependencies?");
        }

        $installer_input = new ArrayInput(array(
            'command' => 'install',
            '--dbHost'  => $this->config->getDbHost(),
            '--dbPort'  => $this->config->getDbPort(),
            '--dbUser'  => $this->config->getDbUser(),
            '--dbPass'  => $this->config->getDbPass(),
            '--dbName'  => $data['db_name'],
            '--installSampleData' => ($data['sample_data']) ? 'yes' : 'no',
            '--useDefaultConfigParams' => 'yes',
            '--magentoVersion' => $data['version'],
            '--installationFolder' => $data['install_dir'],
            '--baseUrl' => $data['base_url']
        ));

        // InstallCommand uses argv to get db credentials instead of input, so set those
        $installer->setCliArguments(explode(" ", $installer_input->__toString()));

        $installer->run($installer_input, $this->getOutput());
    }

    /**
     * Install modman extensions for the specified target.
     *
     * @param array $data Target data.
     */
    protected function installExtensions($data) {
        if (count($data['extensions']) == 0) {
            return;
        }

        $output = $this->getOutput();

        $modman = new Modman($data['install_dir'], $output->isQuiet());

        foreach ($data['extensions'] as $extension) {
            if (!$output->isQuiet()) {
                $output->writeln(sprintf("Installing '%s' extension.", $extension));
            }
            $modman->install($extension);
            if (!$output->isQuiet()) {
                $output->writeln(implode("\n", $modman->getLastOutput()));
            }
        }
    }

    protected function configure() {
        parent::configure();

        $this
            ->setName('install')
            ->setDescription('Install one or more configured targets.');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        foreach ($this->targets as $target) {
            if (!$output->isQuiet()) {
                $output->writeln(sprintf("Installing target '%s'...", $target));
            }

            $target_data = $this->config->getTarget($target);

            $this->installMagento($target_data);
            $this->installExtensions($target_data);

            if (!$output->isQuiet()) {
                $output->writeln(sprintf("Target '%s' installed.", $target));
            }
        }
    }
}
