<?php

/**
 * Created by PhpStorm.
 * User: marcel
 * Date: 26.05.16
 * Time: 12:45
 */
class SizableColumn extends GeneralColumn {
    /**
     * @var int $length
     */
    private $length;

    /**
     * SizableColumn constructor.
     *
     * @param string     $name
     * @param ColumnType $type
     * @param bool       $length
     */
    public function __construct ($name,
                                 ColumnType $type,
                                 $length) {
        $this->length = $length;
        parent::__construct($name, $type);
    }

    /**
     * @return int $length
     */
    public function getLength () {
        return $this->length;
    }
}