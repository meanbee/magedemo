<?php
namespace Meanbee\MageDemo;

use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Application extends \Symfony\Component\Console\Application {

    const APP_NAME = "Mage Demo";
    const APP_VERSION = "1.0.0";

    public function __construct() {
        parent::__construct(self::APP_NAME, self::APP_VERSION);
    }
}
