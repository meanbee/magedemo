<?php
namespace Meanbee\MageDemo;

class Application extends \Symfony\Component\Console\Application {

    const APP_NAME = "Mage Demo";
    const APP_VERSION = "1.0.0";

    public function __construct() {
        parent::__construct(self::APP_NAME, self::APP_VERSION);
    }
}
