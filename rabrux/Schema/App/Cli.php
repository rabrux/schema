<?php
/**
 * Schema Cli Migration - PHP command line schema migration
 *
 * @author      Raúl Salvador Andrade <rsalvador@wat.mx>
 * @copyright   2015 Raúl Salvador Andrade
 * @link        http://www.wat.mx
 * @license     http://www.wat.mx/license
 * @version     1.0.0
 * @package     Schemando
 *
 * MIT LICENSE
 *
 * Permission is hereby granted, free of charge, to any person obtaining
 * a copy of this software and associated documentation files (the
 * "Software"), to deal in the Software without restriction, including
 * without limitation the rights to use, copy, modify, merge, publish,
 * distribute, sublicense, and/or sell copies of the Software, and to
 * permit persons to whom the Software is furnished to do so, subject to
 * the following conditions:
 *
 * The above copyright notice and this permission notice shall be
 * included in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 * NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
 * LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
 * OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
 * WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

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

        case 'remove':
          $this->removeCmd();
          break;

        case 'execute':
          $this->executeCmd();
          break;

        case 'help':
          $this->helpCmd();
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
      echo Messages::buildMessage(Messages::PROMPT, 'driver', 'mysql');
      $driver = trim(fgets(STDIN));
      $servers[$name]['driver'] = $driver !== '' ? $driver : 'mysql';
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

  /**
   * Execute actions, dump schemas to database server
   */
  protected function executeCmd() {

    $schema = $this->getArgument();

    if (!$schema) {
      echo Messages::buildMessage(Messages::ERROR, 'migration schema not specified');
      return false;
    }

    if (!$this->fileManager->schemaExists($schema)) {
      echo Messages::buildMessage(Messages::ERROR, 'migration schema not found');
      return false;
    }

    $conf = $this->fileManager->readJSON('.conf.json');

    if ( !$conf ) {
      echo Messages::buildMessage(Messages::WARNING, 'config file have a syntax errors');
      return false;
    }

    $migration = $this->fileManager->readJSON("$schema.json");

    if ( !$migration ) {
      echo Messages::buildMessage(Messages::WARNING, 'migration schema have syntas errors');
      return false;
    }

    do {
      echo Messages::buildMessage(null, 'please select the server to migrate selected schema:');
      echo Messages::buildMessage(null);
      $i = 0;
      foreach ($conf as $server => $params) {
        $option[] = $server;
        echo Messages::buildMessage(Messages::OPTION, $server, $i++);
      }
      echo Messages::buildMessage(Messages::PROMPT, 'option');
      $response = $option[strtolower( trim( fgets( STDIN ) ) )];
    } while ( $response == NULL );

    $server = $conf->$response;

    if ($server->driver == 'mysql') {
      try {
        $pdo = new \PDO(
          // 'mysql:host=' . $server->host . ';dbname=' . $migration->database->name,
          'mysql:host=' . $server->host,
          $server->username,
          $server->password,
          array(
            \PDO::ATTR_TIMEOUT => '15'
          )
        );
      } catch( \PDOException $e ) {
        die( Messages::buildMessage(Messages::ERROR, $e->getMessage()) );
      }

      if ($pdo) {
        $dbm = new \Schema\DBM\Driver\MySQL($pdo);

        if ( $dbm->database($migration->database->name)->exists() ) {

          echo Messages::buildMessage(Messages::WARNING, 'database exists');
          echo Messages::buildMessage(Messages::QUESTION, 'would you like to overwrite', 'Y/n');
          // If not overwrite exit
          if (strtolower( trim( fgets( STDIN ) ) ) !== 'y')
            return false;

        }

        if ( !$dbm->database($migration->database->name)->create() ) {
          echo Messages::buildMessage(Messages::WARNING, 'database not created');
          return false;
        }

        if ( !$dbm->migrate($migration) ) {
          echo Messages::buildMessage(Messages::ERROR, 'migration can not be executed, migration file has errors');
          echo Messages::buildMessage(null, 'please check the documentation');
        } else
          echo Messages::buildMessage(Messages::SUCCESS, 'migration was uploaded');

      }
    }

  }

  /**
   * remove command
   */
  protected function removeCmd() {

    $arg = $this->getArgument();

    switch ( $arg ) {
      case 'server':
        $this->removeServer();
        break;

      case 'migration':
        $this->removeMigration();
        break;

      default:
        $this->unknownCommand($arg);
        break;
    }


  }

  /**
   * Remove migration schema
   */
  protected function removeMigration() {
    $schema = $this->getArgument();

    if ($schema) {
      if ( $this->fileManager->schemaExists($schema)) {
        echo Messages::buildMessage(Messages::QUESTION, "would you like to remove $schema migration", 'Y/n');
        if (strtolower(trim(fgets(STDIN))) === 'y')
          if ( $this->fileManager->removeSchema($schema) )
            echo Messages::buildMessage(Messages::SUCCESS, 'migration was removed');
          else {
            echo Messages::buildMessage(Messages::ERROR, 'migration can not be removed');
            echo Messages::buildMessage(null, 'please check if schemas directory is writable.');
          }
      } else {
        echo Messages::buildMessage(Messages::ERROR, 'migration not exists');
        echo Messages::buildMessage(null, 'please list migrations before remove');
      }
    } else
      echo Messages::buildMessage(Messages::ERROR, 'migration name not specified');
  }

  protected function removeServer() {
    // getting the servername to be removed
    $serverName = $this->getArgument();

    if ( !$this->configExists() )
      return false;

    if ( $serverName ) {
      $conf = $this->fileManager->readJSON('.conf.json', true);

      if ( !$conf ) {
        echo Messages::buildMessage(Messages::WARNING, 'config file have a syntax errors');
        return false;
      }

      if ($conf[$serverName]) {
        unset($conf[$serverName]);
        if ( $this->fileManager->dumpJSON('.conf.json', $conf) )
          echo Messages::buildMessage(Messages::SUCCESS, 'server was deleted');
        else {
          echo Messages::buildMessage(Messages::ERROR, 'config can not be saved');
          echo Messages::buildMessage(null, 'please check if directory is writable');
        }
      } else
        echo Messages::buildMessage(Messages::ERROR, 'the specified server is not present in config file');

    } else
      echo Messages::buildMessage(Messages::ERROR, 'server name not specified');
  }

  /**
   * create command
   */
  public function createCmd() {
    $arg = $this->getArgument();

    if ( !$this->configExists() )
      return false;

    switch ( $arg ) {

      case 'server':
        $this->createServer();
        break;
      case 'migration':
        $this->createMigration();
        break;

      default:
        $this->unknownCommand($arg);
        break;
    }

  }

  /**
   * Add one or more servers to conf file
   */
  protected function createServer() {
    if ( !$this->configExists() )
      return false;

    $conf = $this->fileManager->readJSON('.conf.json', true);

    if ( !$conf ) {
      echo Messages::buildMessage(Messages::WARNING, 'config file have a syntax errors');
      return false;
    }

    $conf = $this->addServers($conf);

    if ( $this->fileManager->dumpJSON('.conf.json', $conf) )
      echo Messages::buildMessage(Messages::SUCCESS, 'configuration file was created without errors');
    else {
      echo Messages::buildMessage(Messages::ERROR, 'config file can not be created');
      echo Messages::buildMessage(null, 'please check if directory is writable');
    }

  }

  /**
   * Create migration, if example is set, it generate a sample schema table into migration
   */
  public function createMigration() {
    $arg = $this->getArgument();

    if ( $arg ) {
      // if schema is authorized to create
      if ( $this->schemaExists($arg) ) {
        echo Messages::buildMessage(null, 'please enter database information:');
        echo Messages::buildMessage(Messages::PROMPT, 'name');
        $schema['database']['name'] = trim(fgets(STDIN));
        echo Messages::buildMessage(Messages::PROMPT, 'collation', 'utf8_unicode_ci');
        $collation = trim(fgets(STDIN));
        $schema['database']['collation'] = $collation == '' ? 'utf8_unicode_ci': $collation;
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
      echo Messages::buildMessage(Messages::ERROR, 'migration name was not specified, use help');
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
    if ($migrations) {
      foreach ($migrations as $file) {
        echo Messages::buildMessage(Messages::OUT, $file);
      }
    } else {
      echo Messages::buildMessage(Messages::WARNING, 'schemas not found');
      echo Messages::buildMessage(null, 'before list schemas, run create migration command');
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

  /**
   * Help
   */
  public function helpCmd() {

    // Get command if is null set help by default
    $command = $this->getArgument();
    $command = $command == null ? 'help' : $command;

    switch ($command) {
      case 'help':
        echo Messages::COLORS['WARNING'] . "Usage:\n";
        echo Messages::COLORS['CONSOLE'] . "help [command]\n";
        echo Messages::COLORS['WARNING'] . "\nArguments:\n";
        echo Messages::COLORS['SUCCESS'] . "command\t\t\t" . Messages::COLORS['CONSOLE'] . "The command name.\n";
        echo Messages::COLORS['WARNING'] . "\nAvailable commands:\n";
        echo Messages::COLORS['SUCCESS'] . "create\t\t\t" . Messages::COLORS['CONSOLE'] . "Create new server or migration.\n";
        echo Messages::COLORS['SUCCESS'] . "execute\t\t\t" . Messages::COLORS['CONSOLE'] . "Dump specified migration schema to server\n";
        echo Messages::COLORS['SUCCESS'] . "help\t\t\t" . Messages::COLORS['CONSOLE'] . "Display help for itself or specified command.\n";
        echo Messages::COLORS['SUCCESS'] . "init\t\t\t" . Messages::COLORS['CONSOLE'] . "Initialize a new migration project\n";
        echo Messages::COLORS['SUCCESS'] . "list\t\t\t" . Messages::COLORS['CONSOLE'] . "List server or migrations\n";
        echo Messages::COLORS['SUCCESS'] . "remove\t\t\t" . Messages::COLORS['CONSOLE'] . "Remove server or migrations\n";
        echo Messages::COLORS['WARNING'] . "\nHelp:\n";
        echo Messages::COLORS['CONSOLE'] . " The " . Messages::COLORS['SUCCESS'] . "help" . Messages::COLORS['CONSOLE'] . " command displays help for a given command:\n";
        echo Messages::COLORS['SUCCESS'] . "\n\tphp /path/to/schemando help init\n";
        break;
      case 'init':
        echo Messages::COLORS['WARNING'] . "Usage:\n";
        echo Messages::COLORS['CONSOLE'] . "init\n";
        echo Messages::COLORS['WARNING'] . "\nInit:\n";
        echo Messages::COLORS['CONSOLE'] . "   The " . Messages::COLORS['SUCCESS'] . "init" . Messages::COLORS['CONSOLE'] . " command initializes a new migration schema configuration.\n   It creates schemas directory in the current directory, in this directory will be storage\n the config file call " . Messages::COLORS['SUCCESS'] . ".conf.json" . Messages::COLORS['CONSOLE'] . " and the migration schemas called file.json where file is\n the name of migration. \n";
        echo Messages::COLORS['SUCCESS'] . "\n\tphp /path/to/schemando init\n";
        break;

      case 'create':
        echo Messages::COLORS['WARNING'] . "Usage:\n";
        echo Messages::COLORS['CONSOLE'] . "create [server|migration]\n";
        echo Messages::COLORS['WARNING'] . "\nArguments:\n";
        echo Messages::COLORS['SUCCESS'] . "migration\t\t" . Messages::COLORS['CONSOLE'] . "Creates a new migration schema.\n";
        echo Messages::COLORS['SUCCESS'] . "server\t\t\t" . Messages::COLORS['CONSOLE'] . "Creates a new server access information.\n";
        echo Messages::COLORS['WARNING'] . "\nCreate:\n";
        echo Messages::COLORS['CONSOLE'] . "   The " . Messages::COLORS['SUCCESS'] . "create" . Messages::COLORS['CONSOLE'] . " command have two usages:\n";
        echo Messages::COLORS['SUCCESS'] . "\n a) " . Messages::COLORS['CONSOLE'] . "Create a new migration schema file into directory schemas.\n";
        echo Messages::COLORS['SUCCESS'] . "\n\tphp /path/to/schemando create migration [migration_name] [example]\n";
        echo Messages::COLORS['WARNING'] . "\n\tArguments:\n";
        echo Messages::COLORS['SUCCESS'] . "\tmigration_name\t\t" . Messages::COLORS['CONSOLE'] . "the name of schemas file.\n";
        echo Messages::COLORS['SUCCESS'] . "\texample\t\t\t" . Messages::COLORS['CONSOLE'] . "create example data into migration file (optional).\n";
        echo Messages::COLORS['SUCCESS'] . "\n b) " . Messages::COLORS['CONSOLE'] . "Create a new server access information.\n";
        echo Messages::COLORS['SUCCESS'] . "\n\tphp /path/to/schemando create server\n";
        break;

      case 'execute':
        echo Messages::COLORS['WARNING'] . "Usage:\n";
        echo Messages::COLORS['CONSOLE'] . "execute [migration_name]\n";
        echo Messages::COLORS['WARNING'] . "\nArguments:\n";
        echo Messages::COLORS['SUCCESS'] . "migration_name\t\t" . Messages::COLORS['CONSOLE'] . "Migration schema to be dumped into host\n";
        echo Messages::COLORS['WARNING'] . "\nExecute:\n";
        echo Messages::COLORS['CONSOLE'] . "   The " . Messages::COLORS['SUCCESS'] . "execute" . Messages::COLORS['CONSOLE'] . " command dump the specified schema migration file to server.\n";
        echo Messages::COLORS['SUCCESS'] . "\n\tphp /path/to/schemando execute dummy\n";
        break;

      case 'list':
        echo Messages::COLORS['WARNING'] . "Usage:\n";
        echo Messages::COLORS['CONSOLE'] . "list [servers|migrations]\n";
        echo Messages::COLORS['WARNING'] . "\nArguments:\n";
        echo Messages::COLORS['SUCCESS'] . "migrations\t\t" . Messages::COLORS['CONSOLE'] . "List all migrations in schemas directory.\n";
        echo Messages::COLORS['SUCCESS'] . "servers\t\t\t" . Messages::COLORS['CONSOLE'] . "List all added servers.\n";
        echo Messages::COLORS['WARNING'] . "\nList:\n";
        echo Messages::COLORS['CONSOLE'] . "   The " . Messages::COLORS['SUCCESS'] . "list" . Messages::COLORS['CONSOLE'] . " command list servers or migrations in the migration schema project.\n";
        echo Messages::COLORS['SUCCESS'] . "\n\tphp /path/to/schemando list servers\n";
        break;

        case 'remove':
          echo Messages::COLORS['WARNING'] . "Usage:\n";
          echo Messages::COLORS['CONSOLE'] . "remove [server|migration] [item_name]\n";
          echo Messages::COLORS['WARNING'] . "\nArguments:\n";
          echo Messages::COLORS['SUCCESS'] . "migration\t\t" . Messages::COLORS['CONSOLE'] . "Creates a new migration schema.\n";
          echo Messages::COLORS['SUCCESS'] . "server\t\t\t" . Messages::COLORS['CONSOLE'] . "Creates a new server access information.\n";
          echo Messages::COLORS['SUCCESS'] . "item_name\t\t" . Messages::COLORS['CONSOLE'] . "The server or migration name to delete.\n";
          echo Messages::COLORS['WARNING'] . "\nRemove:\n";
          echo Messages::COLORS['CONSOLE'] . "   The " . Messages::COLORS['SUCCESS'] . "remove" . Messages::COLORS['CONSOLE'] . " command delete a server or migration specified in item_name argument\n";
          echo Messages::COLORS['SUCCESS'] . "\n\tphp /path/to/schemando remove server local\n";
          break;

      default:
        echo Messages::COLORS['UNKNOWN'] . "Invalid argument\n";
        break;
    }

    $this->arguments = array();
  }

}
