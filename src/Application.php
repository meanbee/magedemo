<?php
namespace Meanbee\MageDemo;

use Meanbee\MageDemo\Command\DumpCommand;
use Meanbee\MageDemo\Command\InstallCommand;

class Application extends \Symfony\Component\Console\Application {

    const APP_NAME = "Mage Demo";
    const APP_VERSION = "1.0.0";

    protected $autoloader;

    public function __construct($autoloader = null) {
        parent::__construct(self::APP_NAME, self::APP_VERSION);

        if ($autoloader !== null) {
            $this->setAutoloader($autoloader);
        }

        $this->add(new InstallCommand());
        $this->add(new DumpCommand());
    }

    public function getAutoloader() {
        return $this->autoloader;
    }

    public function setAutoloader($autoloader) {
        $this->autoloader = $autoloader;

        return $this;
    }
}
