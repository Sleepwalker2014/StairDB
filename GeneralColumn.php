<?php

/**
 * Created by PhpStorm.
 * User: marcel
 * Date: 26.05.16
 * Time: 12:45
 */
class GeneralColumn {
    /**
     * @var string $name
     */
    private $name;

    /**
     * @var ColumnType
     */
    private $type;

    /**
     * GeneralColumn constructor.
     *
     * @param string     $name
     * @param ColumnType $type
     */
    public function __construct ($name,
                                 ColumnType $type) {
        $this->name = $name;
        $this->type = $type;
    }

    /**
     * @return string $name
     */
    public function getName () {
        return $this->name;
    }

    /**
     * @return ColumnType
     */
    public function getType () {
        return $this->type;
    }
}