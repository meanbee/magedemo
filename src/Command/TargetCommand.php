<?php
namespace Meanbee\MageDemo\Command;

use Meanbee\MageDemo\Config;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Parser;

class TargetCommand extends Command {

    /** @var InputInterface $input */
    protected $input;
    /** @var OutputInterface $output */
    protected $output;

    /** @var \Meanbee\MageDemo\Config $config */
    protected $config;

    /** @var \N98\Magento\Application $magerun */
    protected $magerun;

    protected $targets;

    /**
     * Set the input interface for this command.
     *
     * @param InputInterface $input
     *
     * @return $this
     */
    public function setInput(InputInterface $input) {
        $this->input = $input;

        return $this;
    }

    /**
     * Get the input interface.
     *
     * @return InputInterface
     */
    public function getInput() {
        return $this->input;
    }

    /**
     * Set the output interface for this command.
     *
     * @param OutputInterface $output
     *
     * @return $this
     */
    public function setOutput(OutputInterface $output) {
        $this->output = $output;

        return $this;
    }

    /**
     * Get the output interface.
     *
     * @return OutputInterface
     */
    public function getOutput() {
        return $this->output;
    }

    /**
     * Get an instance of the Magerun app.
     *
     * @return \N98\Magento\Application
     */
    public function getMagerun() {
        if (!$this->magerun) {
            $this->magerun = new \N98\Magento\Application($this->getApplication()->getAutoloader());
            $this->magerun->init();
        }

        return $this->magerun;
    }

    protected function configure() {
        parent::configure();

        $this
            ->addOption('config', 'c', InputOption::VALUE_REQUIRED, 'Configuration file to use.')
            ->addOption('all', null, InputOption::VALUE_NONE, 'Run the command for all defined targets.')
            ->addArgument('target', InputArgument::IS_ARRAY, 'List of targets to run the command for.');
    }

    protected function initialize(InputInterface $input, OutputInterface $output) {
        parent::initialize($input, $output);

        $this->setInput($input);
        $this->setOutput($output);
        $this->loadConfigFile($input);
        $this->addTargets($input);
    }

    /**
     * Load the configuration from a file specified by the --config parameter or the default file.
     *
     * @param InputInterface $input
     *
     * @throws \Exception
     */
    protected function loadConfigFile(InputInterface $input) {
        $config_file = ($input->hasOption('config')) ? $input->getOption('config') : Config::DEFAULT_CONFIG_FILE;

        if (!file_exists($config_file)) {
            throw new \Exception(sprintf("Configuration file '%s' does not exist.", $config_file));
        }

        if (!is_readable($config_file)) {
            throw new \Exception(sprintf("Configuration file '%s' is not readable.", $config_file));
        }

        $parser = new Parser();
        $config_data = $parser->parse(file_get_contents($config_file));

        $this->config = new Config($config_data);
    }

    /**
     * Determine which targets the command should be run for by examining the
     * --all option or the argument list.
     *
     * @param InputInterface $input
     *
     * @throws \Exception
     */
    protected function addTargets(InputInterface $input) {
        if ($input->getOption('all')) {
            $this->targets = array_keys($this->config->getTargets());
        } else if ($input->getArgument('target')) {
            $this->targets = $input->getArgument('target');
            foreach ($this->targets as $target) {
                if (!$this->config->hasTarget($target)) {
                    throw new \Exception(sprintf("Target '%s' does not exist.", $target));
                }
            }
        } else {
            throw new \Exception("No targets to run. You must specify either --all or a list of targets to run the command for.");
        }
    }
}
