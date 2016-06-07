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
require_once 'php/Set.php';
require_once 'vendor/autoload.php';

/**
 * Created by PhpStorm.
 * User: marcel
 * Date: 24.05.16
 * Time: 16:15
 */
$pdo = new PDO('mysql:host=localhost;dbname=animal', 'root', 'Deutschrock');


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
        case 'optimizer_prune_level':
            return new TextInput($variableValue, '');
        case 'optimizer_search_depth':
            return new TextInput($variableValue, '');
        case 'optimizer_switch':
            return new TextInput($variableValue, '');
        case 'performance_schema':
            return new TextInput($variableValue, '');
        case 'performance_schema_events_waits_history_long_size':
            return new TextInput($variableValue, '');
        case 'performance_schema_events_waits_history_size':
            return new TextInput($variableValue, '');
        case 'performance_schema_max_cond_classes':
            return new TextInput($variableValue, '');
        case 'performance_schema_max_cond_instances':
            return new TextInput($variableValue, '');
        case 'performance_schema_max_file_classes':
            return new TextInput($variableValue, '');
        case 'performance_schema_max_file_handles':
            return new TextInput($variableValue, '');
        case 'performance_schema_max_file_instances':
            return new TextInput($variableValue, '');
        case 'performance_schema_max_mutex_classes':
            return new TextInput($variableValue, '');
        case 'performance_schema_max_mutex_instances':
            return new TextInput($variableValue, '');
        case 'performance_schema_max_rwlock_classes':
            return new TextInput($variableValue, '');
        case 'performance_schema_max_rwlock_instances':
            return new TextInput($variableValue, '');
        case 'performance_schema_max_table_handles':
            return new TextInput($variableValue, '');
        case 'performance_schema_max_table_instances':
            return new TextInput($variableValue, '');
        case 'performance_schema_max_thread_classes':
            return new TextInput($variableValue, '');
        case 'performance_schema_max_thread_instances':
            return new TextInput($variableValue, '');
        case 'pid_file':
            return new DirectoryPicker($variableValue, '/lib/plugin/');
        case 'plugin_dir':
            return new DirectoryPicker($variableValue, '/lib/plugin/');
        case 'port':
            return new NumberPicker($variableValue, 0, 65535, 3306);
        case 'preload_buffer_size':
            return new NumberPicker($variableValue, 1024, 1073741824, 32768);
        case 'profiling':
            return new checkbox($variableValue, false);
        case 'protocol_version':
            return new TextInput($variableValue, '');
        case 'proxy_user':
            return new TextInput($variableValue, '');
        case 'pseudo_slave_mode':
            return new checkbox($variableValue, false);
        case 'pseudo_thread_id':
            return new TextInput($variableValue, '');
        case 'query_alloc_block_size':
            return new NumberPicker($variableValue, 1024, 18446744073709551615, 8192);
        case 'query_cache_limit':
            return new NumberPicker($variableValue, 0, 18446744073709551615, 1048576);
        case 'query_cache_min_res_unit':
            return new NumberPicker($variableValue, 512, 18446744073709551615, 4096);
        case 'query_cache_size':
            return new NumberPicker($variableValue, 512, 18446744073709551615, 4096);
        case 'query_cache_type':
            return new Enumerator($variableValue, 0, [0, 1, 2]);
        case 'query_cache_wlock_invalidate':
            return new checkbox($variableValue, false);
        case 'read_only':
            return new checkbox($variableValue, false);
        case 'query_prealloc_size':
            return new NumberPicker($variableValue, 8192, 18446744073709551615, 8192);
        case 'range_alloc_block_size':
            return new NumberPicker($variableValue, 4096, 18446744073709547520, 4096);
        case 'read_buffer_size':
            return new NumberPicker($variableValue, 8200, 2147479552, 131072);
        case 'read_rnd_buffer_size':
            return new NumberPicker($variableValue, 1, 2147483647, 262144);
        case 'relay_log':
            return new DirectoryPicker($variableValue, 'localhost-relay-bin');
        case 'relay_log_index':
            return new DirectoryPicker($variableValue, 'localhost-relay-bin.index');
        case 'relay_log_info_file':
            return new DirectoryPicker($variableValue, 'relay-log.info');
        case 'relay_log_purge':
            return new checkbox($variableValue, true);
        case 'relay_log_space_recovery':
            return new checkbox($variableValue, false);
        case 'relay_log_space_limit':
            return new NumberPicker($variableValue, 0, 18446744073709551615, 0);
        case 'report_host':
            return new TextInput($variableValue, '');
        case 'report_password':
            return new TextInput($variableValue, '');
        case 'report_port':
            return new TextInput($variableValue, '');
        case 'report_user':
            return new TextInput($variableValue, true);
        case 'secure_auth':
            return new checkbox($variableValue, true);
        case 'server_id':
            return new NumberPicker($variableValue, 0, 4294967295, 0);
        case 'skip_external_locking':
            return new checkbox($variableValue, true);
        case 'skip_name_resolve':
            return new checkbox($variableValue, false);
        case 'skip_networking':
            return new checkbox($variableValue, false);
        case 'skip_show_database':
            return new checkbox($variableValue, false);
        case 'slave_compressed_protocol':
            return new checkbox($variableValue, false);
        case 'slave_exec_mode':
            return new Enumerator($variableValue,
                                  'STRICT',
                                  ['STRICT', 'IDEMPOTENT']);
        case 'slave_load_tmpdir':
            return new DirectoryPicker($variableValue, '/tmp');
        case 'slave_max_allowed_packet':
            return new NumberPicker($variableValue, 1024, 1073741824, 1073741824);
        case 'slave_net_timeout':
            return new NumberPicker($variableValue, 1, 18446744073709551615, 60);
        case 'slave_transaction_retries':
            return new NumberPicker($variableValue, 0, 18446744073709551615, 10);
        case 'slave_type_conversions':
            return new Set($variableValue, '', ['ALL_LOSSY', 'ALL_NON_LOSSY', 'ALL_SIGNED', 'ALL_UNSIGNED']);
        case 'slow_launch_time':
            return new NumberPicker($variableValue, 1, 18446744073709551615, 2);
        case 'slow_query_log':
            return new DirectoryPicker($variableValue, 'host_name-slow.log');
        case 'socket':
            return new DirectoryPicker($variableValue, '/tmp/mysql.sock');
        case 'sort_buffer_size':
            return new NumberPicker($variableValue, 32768, 18446744073709551615, 262144);
        case 'sql_auto_is_null':
            return new checkbox($variableValue, false);
        case 'sql_big_selects':
            return new checkbox($variableValue, true);
        case 'sql_buffer_result':
            return new checkbox($variableValue, false);
        case 'sql_log_bin':
            return new checkbox($variableValue, true);
        case 'sql_log_off':
            return new checkbox($variableValue, false);
        case 'sql_notes':
            return new checkbox($variableValue, true);
        case 'sql_quote_show_create':
            return new checkbox($variableValue, true);
        case 'sql_safe_updates':
            return new checkbox($variableValue, false);
        case 'sql_select_limit':
            return new NumberPicker($variableValue, 0, 18446744073709551615, 10000);
        case 'sql_warnings':
            return new checkbox($variableValue, false);
        case 'ssl_ca':
            return new DirectoryPicker($variableValue, false);
        case 'ssl_capath':
            return new DirectoryPicker($variableValue, false);
        case 'ssl_key':
            return new DirectoryPicker($variableValue, false);
        case 'ssl_cipher':
            return new TextInput($variableValue, '');
        case 'ssl_cert':
            return new DirectoryPicker($variableValue, false);
        case 'storage_engine':
            return new TextInput($variableValue, '');
        case 'stored_program_cache':
            return new NumberPicker($variableValue, 0, 524288, 0);
        case 'sync_binlog':
            return new NumberPicker($variableValue, 0, 4294967295, 0);
        case 'sync_frm':
            return new checkbox($variableValue, true);
        case 'sync_master_info':
            return new NumberPicker($variableValue, 0, 18446744073709551615, 10000);
        case 'sync_relay_log':
            return new NumberPicker($variableValue, 0, 18446744073709551615, 10000);
        case 'sync_relay_log_info':
            return new NumberPicker($variableValue, 0, 18446744073709551615, 10000);
        case 'system_time_zone':
            return new TextInput($variableValue, '');
        case 'interactive_timeout':
            return new NumberPicker($variableValue, 1, 18446744073709551615, 28800);
        case 'wait_timeout':
            return new NumberPicker($variableValue, 1, 31536000, 28800);
        case 'warning_count':
            return new TextInput($variableValue, '');
        case 'time_format':
            return new TextInput($variableValue, '');
        case 'time_zone':
            return new TextInput($variableValue, '');
        case 'timed_mutexes':
            return new checkbox($variableValue, false);
        case 'table_definition_cache':
            return new NumberPicker($variableValue, -1, 400, 524288);
        case 'table_open_cache':
            return new NumberPicker($variableValue, 1, 524288, 2000);
        case 'table_open_cache_instances':
            return new NumberPicker($variableValue, 1, 64, 16);
        case 'thread_cache_size':
            return new NumberPicker($variableValue, -1, 16384, -1);
        case 'thread_concurrency':
            return new NumberPicker($variableValue, 1, 512, 10);
        case 'thread_handling':
            return new Enumerator($variableValue,
                                  'one-thread-per-connection',
                                  ['no-threads', 'one-thread-per-connection', 'dynamically-loaded']);
        case 'thread_stack':
            return new NumberPicker($variableValue, 131072, 18446744073709551615, 262144);
        case 'tmp_table_size':
            return new NumberPicker($variableValue, 1024, 18446744073709551615, 16777216);
        case 'tmpdir':
            return new DirectoryPicker($variableValue, false);
        case 'transaction_alloc_block_size':
            return new NumberPicker($variableValue, 1024, 18446744073709551615, 8192);
        case 'transaction_prealloc_size':
            return new NumberPicker($variableValue, 1024, 18446744073709551615, 4096);
        case 'tx_isolation':
            return new Enumerator($variableValue,
                                  'REPEATABLE-READ',
                                  ['READ-UNCOMMITTED', 'READ-COMMITTED', 'REPEATABLE-READ', 'SERIALIZABLE']);
        case 'unique_checks':
            return new checkbox($variableValue, true);
        case 'updatable_views_with_limit':
            return new checkbox($variableValue, true);
        case 'version':
            return new TextInput($variableValue, '');
        case 'version_comment':
            return new TextInput($variableValue, '');
        case 'version_compile_machine':
            return new TextInput($variableValue, '');
        case 'version_compile_os':
            return new TextInput($variableValue, '');
        case 'join_buffer_size':
            return new NumberPicker($variableValue, 128, 18446744073709547520, 262144);
        case 'keep_files_on_create':
            return new checkbox($variableValue, false);
        case 'key_buffer_size':
            return new NumberPicker($variableValue, 8, 4294967295, 8388608);
        case 'key_cache_age_threshold':
            return new NumberPicker($variableValue, 100, 18446744073709551615, 300);
        case 'key_cache_block_size':
            return new NumberPicker($variableValue, 512, 16384, 1024);
        case 'key_cache_division_limit':
            return new NumberPicker($variableValue, 1, 10, 100);
        case 'large_files_support':
            return new checkbox($variableValue, false);
        case 'large_page_size':
            return new checkbox($variableValue, false);
        case 'large_pages':
            return new checkbox($variableValue, false);
        case 'last_insert_id':
            return new TextInput($variableValue, '');
        case 'lc_messages':
            return new Enumerator($variableValue, 'en_US', ['en_US', 'de_DE', 'DISABLED']);
        case 'lc_messages_dir':
            return new DirectoryPicker($variableValue, false);
        case 'lc_time_names':
            return new Enumerator($variableValue, 'en_US', ['en_US', 'de_DE', 'DISABLED']);
        case 'license':
            return new TextInput($variableValue, '');
        case 'local_infile':
            return new checkbox($variableValue, false);
        case 'lock_wait_timeout':
            return new NumberPicker($variableValue, 1, 31536000, 31536000);
        case 'locked_in_memory':
            return new checkbox($variableValue, false);
        case 'log_backward_compatible_user_definitions':
            return new checkbox($variableValue, false);
        case 'log_bin_trust_function_creators':
            return new checkbox($variableValue, false);
        case 'log_builtin_as_identified_by_password':
            return new checkbox($variableValue, false);
        case 'log_error_verbosity':
            return new Enumerator($variableValue, 3, [1, 2, 3]);
        case 'log_output':
            return new Enumerator($variableValue, 'FILE', ['TABLE', 'FILE', 'NONE']);
        case 'log_queries_not_using_indexes':
            return new checkbox($variableValue, false);
    }

    return new checkbox(false, false);
}
