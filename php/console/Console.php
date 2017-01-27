<?php
namespace php\console\Console;

use PDO;
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
            $serverVariables = $this->getServerVariables(new PDO('mysql:host=localhost;dbname=animal', 'root', 'Deutschrock1'));

            if ($this->consoleArguments[2] === self::XML_DUMP_PARAMETER) {
                $filePath = null;
                if (!empty($this->consoleArguments[3])) {
                    $filePath = $this->consoleArguments[3];
                }
                echo $filePath;

                $this->dumpConfigIntoXML($serverVariables, $filePath);

                exit(0);
            }

            $this->showConfig(new PDO('mysql:host=localhost;dbname=animal', 'root', 'Deutschrock1'));

            exit(0);
        }

        if ($this->consoleArguments[1] === self::IMPORT_CONFIG_PARAMETER) {
            $serverVariables = $this->getServerVariablesByXML($this->consoleArguments[2]);

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
     * @return []mixed $consoleArguments - the console arguments
     */
    public function getArgv () {
        return $this->consoleArguments;
    }
}