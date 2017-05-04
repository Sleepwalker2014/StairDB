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
    const ID_PARAMETER = '--id';

    const GREEN_TEXT = "\033[0;32m";
    const RED_TEXT = "\033[032;41m";
    const WHITE_TEXT = "\033[0m";

    const STANDARD_CONFIG_PATH = 'stairdb_conf.xml';

    private $consoleArguments;
    const XML_STANDARD_DUMP_PATH = '/tmp/stair.xml';

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

    public function writeGreenText ($string) {
        echo self::GREEN_TEXT.$string.self::WHITE_TEXT;
    }

    public function writeRedText ($string) {
        echo self::RED_TEXT.$string.self::WHITE_TEXT;
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
            if (empty($this->consoleArguments[2]) ||
                $this->consoleArguments[2] !== self::ID_PARAMETER ||
                empty($this->consoleArguments[3])) {

                echo 'Bitte geben Sie eine Datenbank ID mit dem Parameter --id an'.PHP_EOL;

                exit(1);
            }

            $connectionId = $this->consoleArguments[3];

            $pdoConnection = $this->getConnectionsByXML(self::STANDARD_CONFIG_PATH, $connectionId);

            if (empty($pdoConnection)) {
                $this->writeRedText('Es wurde keine Verbindung mit der ID '.$connectionId.' gefunden');
                echo PHP_EOL.PHP_EOL;

                $this->dumpConnectionsToConsole(self::STANDARD_CONFIG_PATH);

                exit(1);
            }

            $serverVariables = $this->getServerVariables($pdoConnection[0]);

            if ($this->consoleArguments[3] === self::XML_DUMP_PARAMETER) {
                $xmlOutputPath = null;
                if (!empty($this->consoleArguments[3])) {
                    $xmlOutputPath = $this->consoleArguments[3];
                }

                $this->dumpConfigIntoXML($serverVariables, $xmlOutputPath);

                exit(0);
            }

            $this->showConfig($pdoConnection[0]);

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

        echo 'Verwendung: stairdb [--version] [--help] [--dump-conf]'.'
              ['.self::ADD_CONNECTION_PARAMETER.'] ['.self::DIFF_DATABASE_PARAMETER.']'.PHP_EOL;
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


    public function getConnectionById (PDO $pdo) {
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

    public function isValidIp ($ip) {
        if (filter_var($ip, FILTER_VALIDATE_IP) || $ip === 'localhost') {
            return true;
        }

        return false;
    }

    public function getConsoleParameterIfExisting ($parameter) {
        $idParameterIndex = array_search($parameter, $this->consoleArguments);

        if ($idParameterIndex && isset($this->consoleArguments[$idParameterIndex + 1])) {
            return $this->consoleArguments[$idParameterIndex + 1];
        }

        return null;
    }

    public function addConnectionToXML ($filePath = null) {
        if (empty($filePath)) {
            $filePath = self::STANDARD_CONFIG_PATH;
        }

        if (!file_exists($filePath)) {
            $this->xmlWriter->openURI($filePath);
            $this->xmlWriter->startDocument('1.0');
            $this->xmlWriter->setIndent(true);

            $this->xmlWriter->startElement('connections');

            $this->xmlWriter->fullEndElement();

            $this->xmlWriter->endDocument();
            $this->xmlWriter->flush();

            $this->writeGreenText('Die Datenbankeinstellungen wurden erfolgreich nach '.$filePath.' exportiert'.PHP_EOL);
        }

        $id = $this->getConsoleParameterIfExisting('-id');
        if (!$id) {
            echo 'Bitte geben Sie über den Parameter -id eine beliebige ID für die neue Datenverbindung an.'.PHP_EOL;

            exit(1);
        }

        $host = $this->getConsoleParameterIfExisting('-host');
        if (!$host) {
            echo 'Bitte geben Sie über den Parameter -host eine gültige IP Addresse ein.'.PHP_EOL;

            exit(1);
        }

        if (!$this->isValidIp($host)) {
            echo 'Der angegebene Host '.$host.' ist keine gültige IP Adresse.'.PHP_EOL;

            exit(1);
        }

        $database = $this->getConsoleParameterIfExisting('-database');
        if (!$database) {
            echo 'Bitte geben Sie über den Parameter -database einen Datenbanknamen ein.'.PHP_EOL;

            exit(1);
        }

        $user = $this->getConsoleParameterIfExisting('-user');
        if (!$user) {
            echo 'Bitte geben Sie über den Parameter -user einen Benutzernamen ein.'.PHP_EOL;

            exit(1);
        }

        $password = $this->getConsoleParameterIfExisting('-password');
        if (!$password) {
            echo 'Bitte geben Sie über den Parameter -user ein Passwort ein.'.PHP_EOL;

            exit(1);
        }

        $xml = simplexml_load_file($filePath);

        if (!empty($xml->xpath('//connection[@id="'.$id.'"]'))) {
            echo 'Die Verbindung mit der ID '.$id.' existiert schon in der Konfiguration, überschreiben?'.PHP_EOL;

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
            $filePath = self::XML_STANDARD_DUMP_PATH;
        }

        if (!$this->xmlWriter->openURI($filePath)) {
            echo $filePath.' ist kein existenter Pfad';

            exit(1);
        }

        $this->xmlWriter->startDocument('1.0');
        $this->xmlWriter->setIndent(true);

        $this->xmlWriter->startElement('variables');

        foreach ($serverVariables as $variableName => $variableValue) {
            $this->xmlWriter->writeElement($variableName, htmlspecialchars($variableValue));
        }

        $this->xmlWriter->endElement();

        $this->xmlWriter->endDocument();
        $this->xmlWriter->flush();

        $this->writeGreenText('Die Datenbankeinstellungen wurden erfolgreich nach '.$filePath.' exportiert'.PHP_EOL);
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
     *
     * @param string|null $id
     * @return PDO[]
     */
    private function getConnectionsByXML ($xmlPath, $id = null) {
        $pdoConnections = [];

        if (!is_file($xmlPath)) {
            echo 'Die Datei '.$xmlPath.' existiert nicht.'.PHP_EOL;

            exit(1);
        }

        /** @var SimpleXMLElement $xmlConnections */
        $xmlConnections = simplexml_load_file($xmlPath);

        if ($id) {
            $xmlConnections = $xmlConnections->xpath('//connection[@id="'.$id.'"]');
        }

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

    private function dumpConnectionsToConsole ($xmlPath) {
        if (!is_file($xmlPath)) {
            echo 'Die Datei '.$xmlPath.' existiert nicht.'.PHP_EOL;

            exit(1);
        }

        $this->writeGreenText('Es existieren folgende Verbindungen:');
        echo PHP_EOL.PHP_EOL;

        /** @var SimpleXMLElement $xmlConnections */
        $xmlConnections = simplexml_load_file($xmlPath);

        $maxIdLength = 0;
        $maxServerLength = 0;
        $maxDatabaseLength = 0;
        $maxUserLength = 0;
        $maxPortLength = 0;

        foreach ($xmlConnections as $xmlConnection) {
            $connectionAttributes = $xmlConnection->attributes();

            if (strlen($connectionAttributes['id']) > $maxIdLength) {
                $maxIdLength = strlen($connectionAttributes['id']);
            }

            if (strlen($connectionAttributes['database']) > $maxDatabaseLength) {
                $maxDatabaseLength = strlen($connectionAttributes['database']);
            }

            if (strlen($connectionAttributes['server']) > $maxServerLength) {
                $maxServerLength = strlen($connectionAttributes['server']);
            }

            if (strlen($connectionAttributes['port']) > $maxPortLength) {
                $maxPortLength = strlen($connectionAttributes['port']);
            }

            if (strlen($connectionAttributes['user']) > $maxUserLength) {
                $maxUserLength = strlen($connectionAttributes['user']);
            }
        }

        $this->writeGreenText(str_pad('ID', $maxIdLength + 2, ' ').
                              str_pad('Datenbank', $maxDatabaseLength + 2, ' ').
                              str_pad('Server', $maxServerLength + 2, ' ').
                              str_pad('Port', $maxPortLength + 2, ' ').
                              str_pad('Benutzer', $maxUserLength + 2, ' '));
        echo PHP_EOL.PHP_EOL;
        foreach ($xmlConnections as $xmlConnection) {
            $connectionAttributes = $xmlConnection->attributes();

            $this->writeGreenText(str_pad($connectionAttributes['id'], $maxIdLength + 2, ' '));
            $this->writeGreenText(str_pad($connectionAttributes['database'], $maxDatabaseLength + 2, ' '));
            $this->writeGreenText(str_pad($connectionAttributes['server'], $maxServerLength + 2, ' '));
            $this->writeGreenText(str_pad($connectionAttributes['port'], $maxPortLength + 2, ' '));
            $this->writeGreenText(str_pad($connectionAttributes['user'], $maxUserLength + 2, ' '));
            echo PHP_EOL;
        }

        echo PHP_EOL;
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
                echo $this->writeRedText('Es ist ein Problem mit der Datenbankverbindung aufgetreten').PHP_EOL;
        }

        return true;
    }
}