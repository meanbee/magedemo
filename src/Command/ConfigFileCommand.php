<?php
namespace Meanbee\MageDemo\Command;

use Meanbee\MageDemo\Config;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Parser;

class ConfigFileCommand extends Command {

    /** @var \Meanbee\MageDemo\Config $config */
    protected $config;

    protected function configure() {
        parent::configure();

        $this->addOption('config', 'c', InputOption::VALUE_REQUIRED, 'Configuration file to use.');
    }

    protected function initialize(InputInterface $input, OutputInterface $output) {
        parent::initialize($input, $output);

        $this->loadConfigFile($input);
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
}
