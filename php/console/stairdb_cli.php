<?php
/**
 * Created by PhpStorm.
 * User: marcel
 * Date: 18.01.17
 * Time: 12:21
 */
require_once 'Console.php';

use php\console\Console\Console;

$console = new Console($argv, new XMLWriter());