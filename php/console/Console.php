<?php
namespace php\console\Console;

use PDO;
use PDOException;
use SimpleXMLElement;
use XMLReader;
use XMLWriter;

/**
 * Created by PhpStorm.
 * User: marcel
 * Date: 24.01.17
 * Time: 13:06
 */
class Console {
    const VERSION_PARAMETER = '--version';
    const DUMP_CONFIG_PARAMETER = '--dump-conf';
    const IMPORT_CONFIG_PARAMETER = '--import-conf';
    const XML_DUMP_PARAMETER = '--xml';
    const DIFF_DATABASE_PARAMETER = '--diff';
    const ADD_CONNECTION_PARAMETER = '--add-connection';

    const GREEN_TEXT = "\033[0;32m";
    const RED_TEXT = "\033[032;41m";
    const WHITE_TEXT = "\033[0m";

    const STANDARD_CONFIG_PATH = 'stairdb_conf.xml';

    private $consoleArguments;

    /**
     * @var $xmlWriter XMLWriter
     */
    private $xmlWriter;

    /**
     * Console constructor.
     *
     * @param []mixed $argv - the console arguments
     * @param XMLWriter $xmlWriter
     */
    public function __construct ($consoleArgument, XMLWriter $xmlWriter) {
        $this->consoleArguments = $consoleArgument;
        $this->xmlWriter = $xmlWriter;

        $this->routeCommand();
    }

    private function routeCommand () {
        if (empty($this->consoleArguments[1])) {
            $this->showApplicationInfo();

            exit(0);
        }

        if ($this->consoleArguments[1] === self::VERSION_PARAMETER) {
            $this->showVersion();

            exit(0);
        }

        if ($this->consoleArguments[1] === self::DUMP_CONFIG_PARAMETER) {
            $pdoConnections = $this->getConnectionsByXML(self::STANDARD_CONFIG_PATH);
            $serverVariables = $this->getServerVariables($pdoConnections[0]);

            if ($this->consoleArguments[2] === self::XML_DUMP_PARAMETER) {
                $xmlOutputPath = null;
                if (!empty($this->consoleArguments[3])) {
                    $xmlOutputPath = $this->consoleArguments[3];
                }

                $this->dumpConfigIntoXML($serverVariables, $xmlOutputPath);

                exit(0);
            }

            $this->showConfig($pdoConnections[1]);

            exit(0);
        }

        if ($this->consoleArguments[1] === self::DIFF_DATABASE_PARAMETER) {
            $configPath = self::STANDARD_CONFIG_PATH;
            if (!empty($this->consoleArguments[2])) {
                $configPath = $this->consoleArguments[2];
            }

            $diffPdoConnections = $this->getConnectionsByXML($configPath);

            $this->printComparedDatabaseSettings($this->compareDatabaseSettings($diffPdoConnections));

            exit(0);
        }

        if ($this->consoleArguments[1] === self::IMPORT_CONFIG_PARAMETER) {
            $serverVariables = $this->getServerVariablesByXML($this->consoleArguments[2]);

            exit(0);
        }

        if ($this->consoleArguments[1] === self::ADD_CONNECTION_PARAMETER) {
            $this->addConnectionToXML();

            exit(0);
        }

        $this->showApplicationInfo();
    }

    public function showApplicationInfo () {
        echo 'Welcome to StairDB version 1.0'.PHP_EOL.PHP_EOL;

        echo 'Verwendung: stairdb [--version] [--help] [--dump-conf]'.PHP_EOL;
    }

    public function showVersion () {
        echo 'version 1.0'.PHP_EOL;
    }

    public function showConfig (PDO $pdo) {
        $tableQuery = $pdo->prepare('SHOW variables;');
        $tableQuery->execute();

        $results = $tableQuery->fetchAll(PDO::FETCH_ASSOC);

        echo "\033[0;32m";
        foreach ($results as $result) {
            echo str_pad($result['Variable_name'], 55).
                 $result['Value'].PHP_EOL;
        }
        echo "\033[0m";
    }

    public function addConnection (PDO $pdo) {
        $tableQuery = $pdo->prepare('SHOW variables;');
        $tableQuery->execute();

        $results = $tableQuery->fetchAll(PDO::FETCH_ASSOC);

        echo "\033[0;32m";
        foreach ($results as $result) {
            echo str_pad($result['Variable_name'], 55).
                 $result['Value'].PHP_EOL;
        }
        echo "\033[0m";
    }

    public function addConnectionToXML ($filePath = null) {
        if (empty($filePath)) {
            $filePath = 'stairdb_conf.xml';
        }

        $idParameterIndex = array_search('-id', $this->consoleArguments);
        if (!$idParameterIndex || !isset($this->consoleArguments[$idParameterIndex + 1])) {
            return false;
        }

        $id = $this->consoleArguments[$idParameterIndex + 1];

        $hostParameterIndex = array_search('-host', $this->consoleArguments);
        if (!$hostParameterIndex || !isset($this->consoleArguments[$hostParameterIndex + 1])) {
            return false;
        }

        $host = $this->consoleArguments[$hostParameterIndex + 1];

        if (!filter_var($host, FILTER_VALIDATE_IP) && $host !== 'localhost') {
            return false;
        }

        $databaseParameterIndex = array_search('-database', $this->consoleArguments);
        if (!$databaseParameterIndex || !isset($this->consoleArguments[$databaseParameterIndex + 1])) {
            return false;
        }

        $database = $this->consoleArguments[$databaseParameterIndex + 1];

        $userParameterIndex = array_search('-user', $this->consoleArguments);
        if (!$userParameterIndex || !isset($this->consoleArguments[$userParameterIndex + 1])) {
            return false;
        }

        $user = $this->consoleArguments[$userParameterIndex + 1];

        $passwordParameterIndex = array_search('-password', $this->consoleArguments);
        if (!$passwordParameterIndex || !isset($this->consoleArguments[$passwordParameterIndex + 1])) {
            return false;
        }

        $password = $this->consoleArguments[$passwordParameterIndex + 1];

        $xml = simplexml_load_file($filePath);

        if (!empty($xml->xpath('//connection[@id="'.$id.'"]'))) {
            echo 'Die Verbindung mit der ID '.$id.' existiert schon in der Konfiguration, überschreiben?';
            echo PHP_EOL;

            $overwriteConnection = readline('ja/nein');

            if ($overwriteConnection === 'ja') {
                $this->overwriteConnectionToXML($xml, $id, $host, $database, $user, $password, $filePath);
            }

            return true;
        }

        $this->writeConnectionToXML($xml, $id, $host, $database, $user, $password, $filePath);

        return true;
    }

    /**
     * @param SimpleXMLElement $xml
     * @param                  $id
     * @param                  $host
     * @param                  $database
     * @param                  $user
     * @param                  $password
     * @param                  $filePath
     */
    public function writeConnectionToXML (SimpleXMLElement $xml, $id, $host, $database, $user, $password, $filePath) {
        $connection = $xml->addChild('connection');

        $connection->addAttribute('id', $id);
        $connection->addAttribute('server', $host);
        $connection->addAttribute('port', '');
        $connection->addAttribute('database', $database);
        $connection->addAttribute('user', $user);
        $connection->addAttribute('password', $password);

        $xml->asXML($filePath);
    }

    /**
     * @param SimpleXMLElement $xml
     * @param                  $id
     * @param                  $host
     * @param                  $database
     * @param                  $user
     * @param                  $password
     * @param                  $filePath
     */
    public function overwriteConnectionToXML (SimpleXMLElement $xml,
                                              $id,
                                              $host,
                                              $database,
                                              $user,
                                              $password,
                                              $filePath) {
        $changeNode = $xml->xpath('//connection[@id="'.$id.'"]')[0];

        $attributes = $changeNode->attributes();

        $attributes->server = $host;
        $attributes->database = $database;
        $attributes->user = $user;
        $attributes->password = $password;

        $xml->asXML($filePath);
    }

    public function dumpConfigIntoXML (array $serverVariables, $filePath = null) {
        if (empty($filePath)) {
            $filePath = '/tmp/test.xml';
        }

        $this->xmlWriter->openURI($filePath);
        $this->xmlWriter->startDocument('1.0');
        $this->xmlWriter->setIndent(true);

        $this->xmlWriter->startElement('variable');

        foreach ($serverVariables as $variableName => $variableValue) {
            $this->xmlWriter->writeElement($variableName, htmlspecialchars($variableValue));
        }

        $this->xmlWriter->endElement();

        $this->xmlWriter->endDocument();
        $this->xmlWriter->flush();

        echo 'successfully dumped mysql settings into '.$filePath.PHP_EOL;
    }

    private function getServerVariables (PDO $pdo) {
        $serverVariables = [];

        $tableQuery = $pdo->prepare('SHOW variables;');
        $tableQuery->execute();

        $results = $tableQuery->fetchAll(PDO::FETCH_ASSOC);

        foreach ($results as $result) {
            $serverVariables[$result['Variable_name']] = $result['Value'];
        }

        return $serverVariables;
    }

    private function getServerVariablesByXML ($xmlPath) {
        if (!is_file($xmlPath)) {
            echo 'Die Datei '.$xmlPath.' existiert nicht.'.PHP_EOL;

            exit(1);
        }

        $serverVariables = simplexml_load_file($xmlPath);

        foreach ($serverVariables as $variableName => $variableValue) {
            echo $variableName.' '.$variableValue;
            $this->setServerVariable($variableName, $variableValue);
        }

        return $serverVariables;
    }

    private function setServerVariable ($variable, $value) {
        $pdo = new PDO('mysql:host=localhost;dbname=animal', 'root', 'Deutschrock1');

        $pdo->exec('SET GLOBAL '.$variable.' = '.$value);
    }

    /**
     * @param string $xmlPath
     */
    private function getConnectionsByXML ($xmlPath) {
        $pdoConnections = [];

        if (!is_file($xmlPath)) {
            echo 'Die Datei '.$xmlPath.' existiert nicht.'.PHP_EOL;

            exit(1);
        }

        /** @var SimpleXMLElement[] $xmlConnections */
        $xmlConnections = simplexml_load_file($xmlPath);

        foreach ($xmlConnections as $xmlConnection) {
            $connectionAttributes = $xmlConnection->attributes();

            $id = $connectionAttributes['id'];
            $server = $connectionAttributes['server'];
            $port = $connectionAttributes['port'];
            $database = $connectionAttributes['database'];
            $user = $connectionAttributes['user'];
            $password = $connectionAttributes['password'];

            try {
                $pdoConnections[] = new PDO('mysql:host='.$server.';dbname='.$database, $user, $password);
            } catch (PDOException $e) {
                $this->printPDOErrorMessage($e, $user, $database, $password);

                exit(1);
            }
        }

        return $pdoConnections;
    }

    /**
     * @param PDO[] $pdoConnections
     *
     * @return array
     * @internal param string $xmlPath
     */
    private function compareDatabaseSettings (array $pdoConnections) {
        $compareOutput = [];
        $possibleVariables = [];
        $allConnectionVariables = [];

        foreach ($pdoConnections as $pdoConnection) {
            $tmpVariables = $this->getServerVariables($pdoConnection);
            $allConnectionVariables[] = $tmpVariables;

            foreach ($tmpVariables as $tmpVariable => $variableValue) {
                $possibleVariables[$tmpVariable] = $tmpVariable;
            }
        }

        foreach ($possibleVariables as $possibleVariable) {
            $variableDiffers = false;
            $firstVariable = null;

            foreach ($allConnectionVariables as $allConnectionVariableIndex => $connectionVariables) {
                if (!isset($connectionVariables[$possibleVariable])) {
                    $compareOutput[$possibleVariable]['databases'][$allConnectionVariableIndex] = 'N.A.';
                    $variableDiffers = true;
                } else {
                    $compareOutput[$possibleVariable]['databases'][$allConnectionVariableIndex] = $connectionVariables[$possibleVariable];
                }

                if ($firstVariable === null) {
                    $firstVariable = $compareOutput[$possibleVariable]['databases'][$allConnectionVariableIndex];
                }

                if ($firstVariable !== $compareOutput[$possibleVariable]['databases'][$allConnectionVariableIndex]) {
                    $variableDiffers = true;
                }
            }

            $compareOutput[$possibleVariable]['variableDiffers'] = $variableDiffers;
        }

        return $compareOutput;
    }

    /**
     * @param []mixed $compareOutput
     */
    public function printComparedDatabaseSettings (array $compareOutput) {
        foreach ($compareOutput as $variableName => $databaseVariables) {
            $textColour = self::GREEN_TEXT;

            if ($databaseVariables['variableDiffers']) {
                $textColour = self::RED_TEXT;
            }

            $this->setConsoleTextColor($textColour);

            echo str_pad($variableName, 55, ' ', STR_PAD_RIGHT).'| ';

            foreach ($databaseVariables['databases'] as $variableValue) {
                $value = explode(',', $variableValue);

                $longestExplode = count($value);

                echo str_pad($value[0], 40, ' ', STR_PAD_RIGHT).'| ';
            }

            $this->setConsoleTextColor(self::WHITE_TEXT);

            echo PHP_EOL;
        }
    }

    public function setConsoleTextColor ($colour) {
        echo $colour;
    }

    /**
     * @return []mixed $consoleArguments - the console arguments
     */
    public function getArgv () {
        return $this->consoleArguments;
    }

    /**
     * @param PDOException $e
     * @param              $user
     * @param              $database
     * @param              $password
     *
     * @return bool
     */
    private function printPDOErrorMessage (PDOException $e, $user, $database, $password) {
        $errorCode = $e->getCode();

        switch ($errorCode) {
            case 1045:
                echo 'Zugriff verweigert für den Benutzer '.$user.PHP_EOL;
                break;
            case 1044:
                echo 'Zugriff verweigert für den Benutzer '.$user.PHP_EOL;
                break;
            case 2005:
                echo 'Der angegebene Server ist unbekannt oder nicht erreichbar'.PHP_EOL;
                break;
            case 1049:
                echo 'Die angegebene Datenbank '.$database.' existiert nicht'.PHP_EOL;
                break;
            default:
                echo 'Es ist ein Problem mit der Datenbankverbindung aufgetreten';
        }

        return true;
    }
}