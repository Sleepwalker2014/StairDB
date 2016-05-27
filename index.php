<?php
require_once 'ColumnTypeParser.php';
require_once 'ColumnType.php';
require_once 'Column.php';
require_once 'NullableParser.php';
require_once 'vendor/autoload.php';

/**
 * Created by PhpStorm.
 * User: marcel
 * Date: 24.05.16
 * Time: 16:15
 */
$pdo = new PDO('mysql:host=localhost;dbname=animal', 'root', 'Deutschrock1');


$loader = new Twig_Loader_Filesystem('html');
$twig = new Twig_Environment($loader);

$tableQuery = $pdo->prepare("SHOW variables;");
$tableQuery->execute();

$results = $tableQuery->fetchAll(PDO::FETCH_ASSOC);

$tableNames = [];
foreach ($results as $result) {
    foreach ($result as $table) {
        $tableNames[] = $table;
    }
}


/*$tableQuery = $pdo->prepare("SHOW COLUMNS FROM animal.animals");
$tableQuery = $pdo->prepare("SHOW variables;");
$tableQuery->execute();


$tableNames = [];

if ($tableQuery->execute()) {
    while ($row = $tableQuery->fetch(PDO::FETCH_ASSOC)) {
        $columnTypeParser = new ColumnTypeParser($row['Type']);
        $nullableParser = new NullableParser($row['Null']);

        $column = new Column($row['Field'],
                             $columnTypeParser->getType(),
                             $columnTypeParser->getLength(),
                             $columnTypeParser->isUnsigned(),
                             $nullableParser->isNullable());

        echo $column->getName().' '.$column->getType()
                                           ->getType();
        echo '<br>';
    }
}*/

$variables = $pdo->prepare("SHOW variables;");
$variables->execute();

$variables = [];

if ($tableQuery->execute()) {
    while ($row = $tableQuery->fetch(PDO::FETCH_ASSOC)) {
        $variables[$row['Variable_name']] = $row['Value'];
    }
}

echo $twig->render('gui.html', ['variables' => $variables]);
