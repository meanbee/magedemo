<?php
namespace Meanbee\MageDemo;

class Modman {

    const MODMAN_DIR = ".modman";

    protected $directory;

    protected $last_output;
    protected $last_status;

    protected $quiet;

    /**
     * Initialise a modman wrapper.
     *
     * @param string $directory Working directory.
     * @param bool   $quiet     Enable quiet mode.
     */
    public function __construct($directory, $quiet = false) {
        $this->setQuiet($quiet);
        $this->init($directory);
    }

    /**
     * Check if the quiet mode is enabled.
     *
     * @return boolean
     */
    public function isQuiet() {
        return $this->quiet;
    }

    /**
     * Set the quiet mode.
     *
     * @param boolean $quiet
     *
     * @return $this
     */
    public function setQuiet($quiet) {
        $this->quiet = $quiet;

        return $this;
    }

    /**
     * Get the output of the last shell command executed as an array.
     *
     * @return array
     */
    public function getLastOutput() {
        return $this->last_output;
    }

    /**
     * Get the status of the last shell command executed.
     *
     * @return int
     */
    public function getLastStatus() {
        return $this->last_status;
    }

    /**
     * Initialise modman in the given directory.
     *
     * @param $directory
     *
     * @throws \Exception
     */
    public function init($directory) {
        if (!is_dir($directory)) {
            throw new \Exception(sprintf("Directory '%s' does not exist.", $directory));
        }
        if (!is_writeable($directory)) {
            throw new \Exception(sprintf("Directory '%s' is not writeable.", $directory));
        }

        $this->directory = $directory;


        $modman_dir = $directory . DIRECTORY_SEPARATOR . self::MODMAN_DIR;
        if (is_dir($modman_dir)) {
            if (!is_writeable($modman_dir)) {
                throw new \Exception(sprintf("Directory '%s' is not writeable", $modman_dir));
            }
        } else {
            if (!$this->exec("modman init")) {
                throw new \Exception(end(array_values($this->getLastOutput())));
            }
        }
    }

    /**
     * Install the extension from the given git repository.
     *
     * @param string $extension URL to the repository
     * @param bool   $symlink   If true, symlink the extension files instead of copying them (default false).
     * @param bool   $force     If true, overwrite existing files (default false).
     *
     * @return bool
     */
    public function install($extension, $symlink = false, $force = false) {
        $command = array("modman clone");

        if (!$symlink) {
            $command[] = "--copy";
        }

        if ($force) {
            $command[] = "--force";
        }

        $command[] = $extension;

        if ($this->isQuiet()) {
            // Quiet git
            $command[] = "--quiet";
        }

        if (!$this->exec(implode(" ", $command))) {
            throw new \Exception(end(array_values($this->getLastOutput())));
        }
    }

    /**
     * Execute the given shell command in the modman working directory. Return true if the command
     * executed successfully, false otherwise.
     *
     * The command's output and status code can be retrieved with getLastOutput() and getLastStatus().
     *
     * @param $command
     *
     * @return bool
     */
    protected function exec($command) {
        $command = ($this->directory) ? array("cd", $this->directory, "&&", $command) : array($command);

        $this->last_output = array();
        $this->last_status = 0;
        exec(implode(" ", $command), $this->last_output, $this->last_status);

        return ($this->last_status == 0);
    }
}
