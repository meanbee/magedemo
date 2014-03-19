<?php
namespace Meanbee\MageDemo;

class Config {

    const DEFAULT_CONFIG_FILE = "config.yaml";

    const DEFAULT_INSTALL_DIR = "web/";
    const DEFAULT_BACKUP_DIR = "backups/";

    const DEFAULT_DB_HOST   = "localhost";
    const DEFAULT_DB_PORT   = "3306";
    const DEFAULT_DB_USER   = "root";
    const DEFAULT_DB_PASS   = "root";
    const DEFAULT_DB_PREFIX = "magedemo_";

    const DEFAULT_SAMPLE_DATA = true;

    protected $db;
    protected $install_dir;
    protected $backup_dir;
    protected $targets;

    public function __construct($data) {
        $this->db = array(
            'host'   => static::DEFAULT_DB_HOST,
            'port'   => static::DEFAULT_DB_PORT,
            'user'   => static::DEFAULT_DB_USER,
            'pass'   => static::DEFAULT_DB_PASS,
            'prefix' => static::DEFAULT_DB_PREFIX
        );
        if (isset($data['db']) && is_array($data['db'])) {
            $this->db = array_merge($this->db, $data['db']);
        }

        $this->install_dir = (isset($data['install_dir'])) ? $data['install_dir'] : static::DEFAULT_INSTALL_DIR;

        $this->backup_dir = (isset($data['backup_dir'])) ? $data['backup_dir'] : static::DEFAULT_BACKUP_DIR;

        $this->targets = array();
        if (isset($data['targets']) && is_array($data['targets'])) {
            foreach ($data['targets'] as $id => $target) {
                $this->addTarget($id, $target);
            }
        }
    }

    public function getDbHost() {
        return $this->db["host"];
    }

    public function getDbPort() {
        return $this->db["port"];
    }

    public function getDbUser() {
        return $this->db["user"];
    }

    public function getDbPass() {
        return $this->db["pass"];
    }

    public function getDbPrefix() {
        return $this->db["prefix"];
    }

    public function getInstallDir() {
        return $this->install_dir;
    }

    public function getBackupDir() {
        return $this->backup_dir;
    }

    /**
     * Return the data for all configured targets.
     *
     * @return array
     */
    public function getTargets() {
        return $this->targets;
    }

    /**
     * Check if a target with the given id exists.
     *
     * @param $id
     *
     * @return bool
     */
    public function hasTarget($id) {
        return isset($this->targets[$id]);
    }

    /**
     * Return the data for the target with the given id.
     *
     * @param $id
     *
     * @return array
     */
    public function getTarget($id) {
        return $this->targets[$id];
    }

    /**
     * Add a target. Target data parameters include:
     *   version     - magento version to use (required)
     *   sample_data - a flag to install sample data
     *   extensions  - a list of extensions to install
     *
     * @param $id
     * @param $data
     *
     * @return $this
     * @throws \Exception
     */
    public function addTarget($id, $data) {
        if (!isset($data['version'])) {
            throw new \Exception(sprintf("Target '%s' requires the magento version to be specified.", $id));
        }

        $data['sample_data'] = (isset($data['sample_data'])) ? ($data['sample_data'] == true) : static::DEFAULT_SAMPLE_DATA;

        if (!isset($data['extensions'])) {
            $data['extensions'] = array();
        }
        if (!is_array($data['extensions'])) {
            $data['extensions'] = array($data['extensions']);
        }

        $data['db_name'] = $this->getDbPrefix() . $id;

        $this->targets[$id] = $data;

        return $this;
    }
}
