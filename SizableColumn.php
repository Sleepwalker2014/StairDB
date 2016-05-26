<?php

/**
 * Created by PhpStorm.
 * User: marcel
 * Date: 26.05.16
 * Time: 12:45
 */
class SizableColumn extends GeneralColumn {

    /**
     * NumberColumn constructor.
     */
    public function __construct ($name,
                                 ColumnType $type) {
        parent::__construct($name, $type);
    }
}