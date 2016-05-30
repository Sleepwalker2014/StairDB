<?php
require_once 'ColumnTypeParser.php';
require_once 'ColumnType.php';
require_once 'Column.php';
require_once 'NullableParser.php';
require_once 'php/checkbox.php';
require_once 'php/NumberPicker.php';
require_once 'php/DirectoryPicker.php';
require_once 'php/Enumerator.php';
require_once 'php/TextInput.php';
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
        $variables[$row['Variable_name']] = retrieveInputType($row['Variable_name'], $row['Value'])->getHTMLOutput();
    }
}

echo $twig->render('gui.html', ['variables' => $variables]);

function retrieveInputType ($variableName, $variableValue) {
    switch ($variableName) {
        case 'auto_increment_increment':
            return new NumberPicker($variableValue, 1, 65535, 1);
        case 'autocommit':
            return new checkbox($variableValue, false);
        case 'automatic_sp_privileges':
            return new checkbox($variableValue, false);
        case 'basedir':
            return new DirectoryPicker($variableValue, false);
        case 'big-tables':
            return new checkbox($variableValue, false);
        case 'binlog_direct_non_transactional_updates':
            return new checkbox($variableValue, false);
        case 'binlog_format':
            return new Enumerator($variableValue, 'ROW', ['ROW', 'STATEMENT', 'MIXED']);
        case 'binlog_stmt_cache_size':
            return new NumberPicker($variableValue, 4096, 18446744073709551615, 32768);
        case 'bulk_insert_buffer_size':
            return new NumberPicker($variableValue, 4096, 18446744073709551615, 32768);
        case 'completion_type':
            return new Enumerator($variableValue, 'NO_CHAIN', ['NO_CHAIN', 'CHAIN', 'RELEASE', 0, 1, 2]);
        case 'concurrent_insert':
            return new Enumerator($variableValue, 'AUTO', ['NEVER', 'AUTO', 'ALWAYS', 0, 1, 2]);
        case 'connect_timeout':
            return new NumberPicker($variableValue, 2, 31536000, 10);
        case 'datadir':
            return new DirectoryPicker($variableValue, false);
        case 'default_storage_engine':
            return new Enumerator($variableValue, 'InnoDB', ['InnoDB']);
        case 'default_week_format':
            return new Enumerator($variableValue, 0, [0, 1, 2, 3, 4, 5, 6, 7]);
        case 'delay_key_write':
            return new Enumerator($variableValue, 'ON', ['OFF', 'ON', 'ALL']);
        case 'div_precision_increment':
            return new NumberPicker($variableValue, 0, 30, 4);
        case 'error_count':
            return new NumberPicker($variableValue, 0, 100000, 0);
        case 'event_scheduler':
            return new Enumerator($variableValue, 'OFF', ['ON', 'OFF', 'DISABLED']);
        case 'expire_logs_days':
            return new NumberPicker($variableValue, 0, 99, 0);
        case 'flush':
            return new checkbox($variableValue, false);
        case 'flush_time':
            return new NumberPicker($variableValue, 0, 18446744073709551615, 0);
        case 'foreign_key_checks':
            return new checkbox($variableValue, true);
        case 'ft_boolean_syntax':
            return new TextInput($variableValue, '+ -><()~*:""&|');
        case 'ft_max_word_len':
            return new NumberPicker($variableValue, 10, 18446744073709551615, 4);
        case 'ft_min_word_len':
            return new NumberPicker($variableValue, 1, 18446744073709551615, 4);
        case 'ft_query_expansion_limit':
            return new NumberPicker($variableValue, 0, 1000, 20);
        case 'ft_stopword_file':
            return new DirectoryPicker($variableValue, false);
        case 'general_log':
            return new checkbox($variableValue, false);
        case 'general_log_file':
            return new TextInput($variableValue, 'host_name.log');
        case 'group_concat_max_len':
            return new NumberPicker($variableValue, 4, 18446744073709551615, 1024);
        case 'have_compress':
            return new checkbox($variableValue, false);
        case 'have_crypt':
            return new checkbox($variableValue, false);
        case 'have_dynamic_loading':
            return new checkbox($variableValue, false);
        case 'have_geometry':
            return new checkbox($variableValue, false);
        case 'have_innodb':
            return new checkbox($variableValue, false);
        case 'have_ndbcluster':
            return new checkbox($variableValue, false);
        case 'have_openssl':
            return new checkbox($variableValue, false);
        case 'have_partitioning':
            return new checkbox($variableValue, false);
        case 'have_profiling':
            return new checkbox($variableValue, false);
        case 'have_query_cache':
            return new checkbox($variableValue, false);
        case 'have_rtree_keys':
            return new checkbox($variableValue, false);
        case 'have_ssl':
            return new checkbox($variableValue, false);
        case 'have_symlink':
            return new checkbox($variableValue, false);
        case 'hostname':
            return new TextInput($variableValue, 'localhost');
        case 'identity':
            return new TextInput($variableValue, '');
        case 'ignore_builtin_innodb':
            return new checkbox($variableValue, false);
        case 'init_connect':
            return new TextInput($variableValue, '');
        case 'init_file':
            return new DirectoryPicker($variableValue, false);
        case 'init_slave':
            return new TextInput($variableValue, '');
        case 'insert_id':
            return new TextInput($variableValue, '');
        case 'interactive_timeout':
            return new NumberPicker($variableValue, 1, 18446744073709551615, 28800);
    }

    return new checkbox(false, false);
}
