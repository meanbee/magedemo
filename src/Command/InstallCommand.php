<?php
namespace Meanbee\MageDemo\Command;

use Meanbee\MageDemo\Modman;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class InstallCommand extends ConfigFileCommand {

    /** @var InputInterface $input */
    protected $input;
    /** @var OutputInterface $output */
    protected $output;

    /** @var \N98\Magento\Application $magerun */
    protected $magerun;

    public function setInput(InputInterface $input) {
        $this->input = $input;

        return $this;
    }

    public function getInput() {
        return $this->input;
    }

    public function setOutput(OutputInterface $output) {
        $this->output = $output;

        return $this;
    }

    public function getOutput() {
        return $this->output;
    }

    public function getMagerun() {
        if (!$this->magerun) {
            $this->magerun = new \N98\Magento\Application($this->getApplication()->getAutoloader());
            $this->magerun->init();
        }

        return $this->magerun;
    }

    /**
     * Run the installation for the given target.
     *
     * @param $id
     */
    public function installTarget($id) {
        if (!$this->config->hasTarget($id)) {
            return;
        }

        $target = $this->config->getTarget($id);
        $output = $this->getOutput();

        if (!$output->isQuiet()) {
            $output->writeln(sprintf("Installing target '%s'.", $id));
        }

        $this->installMagento($target);
        $this->installExtensions($target);

        if (!$output->isQuiet()) {
            $output->writeln(sprintf("Target '%s' installed.", $id));
        }
    }

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
            ->setDescription('Install a target from the configuration.')
            ->addArgument('target', InputArgument::REQUIRED, "The name of target to install.");
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $this->setInput($input);
        $this->setOutput($output);

        $target = $input->getArgument('target');

        if (!$this->config->hasTarget($target)) {
            throw new \Exception(sprintf("Target '%s' does not exist.", $target));
        }

        $this->installTarget($target);
    }
}
