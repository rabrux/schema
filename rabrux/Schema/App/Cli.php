<?php

namespace Schema\App;

/**
 *
 */
class Cli
{

  public static $arguments;
  public static $fileManager;

  // public static $directory;

  function __construct($argv, $directory)
  {
    $this->fileManager = new Files($directory);
    $this->arguments = $argv;
  }

  /**
   * Get an argument from $arguments
   * @return string
   */
  public function getArgument() {
    return array_shift($this->arguments);
  }

  /**
   * Return $arguments array
   * @return array
   */
  public function getArguments() {
    return $this->arguments;
  }

  /**
   * Run function, starts command iteration
   */
  public function run() {

    echo Messages::buildMessage(null);

    while ( $arg = $this->getArgument() ) {

      switch ($arg) {
        case 'init':
          $this->init();
          break;

        case 'list':
          $this->listCmd();
          break;

        case 'create':
          $this->createCmd();
          break;

        default:
          $this->unknownCommand($arg);
          break;
      }

    }

    echo Messages::buildMessage(null);

  }

  /**
   * Create a new schema project
   */
  protected function init() {
    if ( $this->fileManager->createSchemaDir() ) {
      echo Messages::buildMessage(Messages::SUCCESS, 'schemas directory was created');
      $this->configFile();
    } else {
      echo Messages::buildMessage(Messages::WARNING, 'schemas directory was found, if you continue, the config file will be overwrite');
      echo Messages::buildMessage(Messages::QUESTION, 'would you like to continue', 'y/N');
      if ( strtolower(trim(fgets(STDIN))) === 'y' )
        $this->configFile();
    }
  }

  /**
   * Create main config file
   */
  protected function configFile() {
    echo Messages::buildMessage(null, 'creating main config file');
    // Configure access credentials server details
    $servers = $this->addServers();
    // Write server configuration
    if ( $this->fileManager->dumpJSON('.conf.json', $servers) )
      echo Messages::buildMessage(Messages::SUCCESS, 'configuration file was created without errors');
    else {
      echo Messages::buildMessage(Messages::ERROR, 'config file can not be created');
      echo Messages::buildMessage(null, 'please check if directory is writable');
    }
  }

  /**
   * Add recursive servers
   * @param array
   * @return array
   */
  protected function addServers($servers = []) {
    do {
      echo Messages::buildMessage(null, 'please enter host information:');
      echo Messages::buildMessage(Messages::PROMPT, 'name');
      $name = trim(fgets(STDIN));
      echo Messages::buildMessage(Messages::PROMPT, 'host address');
      $servers[$name]['host'] = trim(fgets(STDIN));
      echo Messages::buildMessage(Messages::PROMPT, 'user');
      $servers[$name]['username'] = trim(fgets(STDIN));
      echo Messages::buildMessage(Messages::PROMPT, 'password');
      $servers[$name]['password'] = trim(fgets(STDIN));
      echo Messages::buildMessage(Messages::QUESTION, 'would you like add another server information', 'Y/n');
      $response = strtolower( trim( fgets( STDIN ) ) );
    } while ( $response == "y" || $response == "" );
    return $servers;
  }

  public function createCmd() {
    $arg = $this->getArgument();

    if ( !$this->configExists() )
      return false;

    switch ( $arg ) {
      case 'migration':
        $this->createMigration();
        break;

      default:
        $this->unknownCommand($arg);
        break;
    }

  }

  public function createMigration() {
    $arg = $this->getArgument();

    if ( $arg ) {
      // if schema is authorized to create
      if ( $this->schemaExists($arg) ) {
        echo Messages::buildMessage(null, 'please enter database information:');
        echo Messages::buildMessage(Messages::PROMPT, 'name');
        $schema['database']['name'] = trim(fgets(STDIN));
        echo Messages::buildMessage(Messages::PROMPT, 'collation', 'utf-8');
        $collation = trim(fgets(STDIN));
        $schema['database']['collation'] = $collation == '' ? 'utf8_general_ci': $collation;
        echo Messages::buildMessage(Messages::PROMPT, 'table prefix');
        $schema['database']['tablePrefix'] = trim(fgets(STDIN));
        echo Messages::buildMessage(Messages::PROMPT, 'engine', 'InnoDB');
        $engine = trim(fgets(STDIN));
        $schema['database']['engine'] = $engine == '' ? 'InnoDB' : $engine;
        // Foreign keys config
        $schema['foreignKeys'] = null;
        // If user set example code
        if ($this->getArgument() == 'example') {
          // Default user table example
          $schema['tables']['user'] = array(
            'fields' => array(
              'id' => 'pk',
              'user' => 'varchar(30) not null',
              'pass' => 'varchar(32) not null',
              'createdAt' => 'int(10) not null',
              'updatedAt' => 'int(10) not null'
            ),
            'keys' => array(
              'unique' => 'user'
            )
          );
        } else
          $schema['tables'] = null;

        // Dump json into file
        if ( $this->fileManager->dumpJSON("$arg.json", $schema) )
          echo Messages::buildMessage(Messages::SUCCESS, 'migration file was created without errors');
        else {
          echo Messages::buildMessage(Messages::ERROR, 'migration file can not be created');
          echo Messages::buildMessage(null, 'please check if directory is writable');
        }
      }
    } else {
      echo Messages::buildMessage(Messages::ERROR, 'migration name was not specified');
      return false;
    }

  }

  /**
   * List function, generates action when argument is known
   */
  public function listCmd() {

    $arg = $this->getArgument();

    // check if config file exists
    if ( !$this->configExists() )
      return false;

    switch ( $arg ) {
      case 'servers':
        $this->listServers();
        break;

      case 'migrations':
        $this->listMigrations();
        break;

      default:
        $this->unknownCommand($arg);
        break;
    }

  }

  /**
   * List servers into a config file
   */
  public function listServers() {
    // Check if config file exists
    if ( !$this->configExists() )
      return false;

    $conf = $this->fileManager->readJSON('.conf.json');

    if ( !$conf ) {
      echo Messages::buildMessage(Messages::WARNING, 'config file have a syntax errors');
      return false;
    }

    foreach ($conf as $server => $params) {
      echo Messages::buildMessage(Messages::OUT, $server);
    }

    return true;

  }

  /**
   * list all migrations on directory schemas
   */
  public function listMigrations() {
    $migrations = ($this->fileManager->getMigrations());
    foreach ($migrations as $file) {
      echo Messages::buildMessage(Messages::OUT, $file);
    }
  }

  /**
   * schema exists
   * returns true if not exists or is athorized to overwrite
   * @return bool
   */
  protected function schemaExists($schema) {
    if ( $this->fileManager->schemaExists($schema)) {
      echo Messages::buildMessage(Messages::WARNING, 'migration exists');
      echo Messages::buildMessage(Messages::QUESTION, 'would you like to overwrite it', 'y/N');
      if (strtolower(trim(fgets(STDIN))) === 'y')
        return true;
      else
        return false;
    }
    return true;
  }

  /**
   * Actions to config file exists
   * @return bool
   */
  protected function configExists() {
    if ( !$this->fileManager->configExists() ) {
      echo Messages::buildMessage(Messages::WARNING, 'config file not exists');
      echo Messages::buildMessage(null, 'please run the init command before this action');
      return false;
    }
    return true;
  }

  /**
   * Unknown command messgae
   */
  public function unknownCommand($command) {
    echo Messages::buildMessage(Messages::UNKNOWN, $command);
    echo Messages::buildMessage(null, 'please use help command.');
  }

}
