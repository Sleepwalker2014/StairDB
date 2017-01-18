<?php
/**
 * Created by PhpStorm.
 * User: marcel
 * Date: 18.01.17
 * Time: 12:21
 */

parseArguments($argv);

/**
 * @param $argv
 */
function parseArguments ($argv) {
    if (empty($argv[1])) {
        showApplicationInfo();
    }

    if ($argv[1] === '--version') {
        showVersion();
    }

    if ($argv[1] === '--dump-conf') {
        $serverVariables = getServerVariables(new PDO('mysql:host=localhost;dbname=animal', 'root', 'Deutschrock1'));

        if ($argv[2] === '--xml') {
            dumpConfigIntoXML($serverVariables);
        }

        dumpConfig(new PDO('mysql:host=localhost;dbname=animal', 'root', 'Deutschrock1'));
    }
}

function showApplicationInfo () {
    echo 'Welcome to StairDB version 1.0'.PHP_EOL
                                         .PHP_EOL;

    echo 'Verwendung: stairdb [--version] [--help] [--dump-conf]'.PHP_EOL;

    exit(1);
}

function showVersion () {
    echo 'version 1.0'.PHP_EOL;

    exit(1);
}

function dumpConfig (PDO $pdo) {
    $tableQuery = $pdo->prepare("SHOW variables;");
    $tableQuery->execute();

    $results = $tableQuery->fetchAll(PDO::FETCH_ASSOC);

    echo "\033[0;32m";
    foreach ($results as $result) {
        echo str_pad($result['Variable_name'], 55).
                     $result['Value'].PHP_EOL;
    }
    echo "\033[0m";

    exit(1);
}

function dumpConfigIntoXML (array $serverVariables, $filePath = "/tmp/mysqlSettingDump.xml") {
    $writer = new XMLWriter();

    $writer->openURI($filePath);
    $writer->startDocument('1.0');
    $writer->setIndent(true);

    $writer->startElement('variable');

    foreach ($serverVariables as $variableName => $variableValue) {
        $writer->writeElement($variableName, htmlspecialchars($variableValue));
    }

    $writer->endElement();

    $writer->endDocument();
    $writer->flush();

    echo 'successfully dumped mysql settings into '.$filePath.PHP_EOL;

    exit(1);
}

function getServerVariables (PDO $pdo) {
    $serverVariables = [];

    $tableQuery = $pdo->prepare("SHOW variables;");
    $tableQuery->execute();

    $results = $tableQuery->fetchAll(PDO::FETCH_ASSOC);

    foreach ($results as $result) {
        $serverVariables[$result['Variable_name']] = $result['Value'];
    }

    return $serverVariables;
}