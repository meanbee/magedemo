magedemo
========

Mage Demo is a PHP application for managing multiple Magento installations.

# Installation

    curl -sS https://getcomposer.org/installer | php -- --install-dir=bin
    bash < <(wget -O - https://raw.github.com/colinmollenhour/modman/master/modman-installer)
    git clone https://github.com/meanbee/magedemo.git magedemo && cd magedemo
    php -f ~/bin/composer.phar install

# Configuration

All of the commands require a configuration file, which specifies parameters, such as database credentials, for the Mage Demo environment and defines all of the Magento installations. By default, the configuration is read from a config.yaml file in the current directory, but an alternative file can be specified using the --config option.

The following configuration options are available in the configuration file:

    db:          - database configuration
      host:      - database server hostname
      port:      - database server port
      user:      - database user credentials
      pass:      - database user credentials
      prefix:    - the prefix to use when creating databases for each Magento install
    install_dir: - the directory to use for Magento installations. Subdirectories will be created for each installation.
    backup_dir:  - the directory to use for storing Magento backups.
    base_url:    - the url prefix to use for each Magento installation when defining their base url
    targets:     - list of Magento installations to manage (more detail in the next section)

## Magento installations

Each Magento installation managed by Mage Demo is configured as an element of the targets list in the configuration file, using the following syntax:

    targets:
      target_name:   - the name of the Magento installation. This is used when creating installation folders, database names and backup files.
        version:     - The Magento version to install, in Magerun format.
        extensions:  - List of Modman extensions to install. Each element in the list should be a git repository url.
        sample_data: - (optional) true or false flag to install sample data with the Magento installation. Defaults to false.
        install_dir: - (optional) Installation directory. Default is a target_name subfolder in the directory defined in install_dir in the main configuration.
        db_name:     - (optional) Database name. Default is target_name prefixed with the database prefix defined in the main configuration.
        base_url:    - (optional) The base url to use. Default is target_name prefixed by the base_url defined in the main configuration.
    
## Databases

The databases for each of the targets need to be manually created and given access to for the user defined in the main configuration.

# Commands

## install [target1 [target2] [target3] [...] | --all]

Create a Magento installations defined by the specified targets in the configuration. This includes, installing the Magento files for the specified version of Magento, creating and installing the database, installing sample data if needed and installing all of the Modman extensions defined.
If --all is specified, the command will install all of the targets defined in the configuration.

## dump [target1 [target2] [target3] [...] | --all]

Create a database backup of the specified targets (or backups for all targets, if --all is used) and store it in the backup directory defined in the configuration.

## restore [target1 [target2] [target3] [...] | --all]

Search for a backups of the specified targets in the backup directory and import them into the target databases.
If --all is specified, the command will go through and restore all of the targets defined in the configuration.

# Cron Setup

To setup a cronjob to automatically restore all of the installations from backup every 3 hours, run the following command:

    crontab -l | { cat; echo "MAILTO=sysadmin@example.com"; echo "0 */3 * * * cd ~/magedemo && php bin/mage-demo restore --all --quiet"; } | crontab -
