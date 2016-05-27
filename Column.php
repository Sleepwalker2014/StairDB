<?php

/**
 * Created by PhpStorm.
 * User: marcel
 * Date: 25.05.16
 * Time: 12:15
 */
class Column {
    private $name;
    /**
     * @var null
     */
    private $extra;
    /**
     * @var bool
     */
    private $nullable;
    private $type;

    /**
     * @var integer
     */
    private $length;

    /**
     * @var bool
     */
    private $unsigned;
    /**
     * @var null
     */
    private $default;

    /**
     * Column constructor.
     *
     * @param            $name
     * @param ColumnType $type
     * @param            $length
     * @param bool       $unsigned
     * @param bool       $nullable
     * @param null       $default
     * @param null       $extra
     */
    public function __construct ($name,
                                 ColumnType $type,
                                 $length,
                                 $unsigned = false,
                                 $nullable = false,
                                 $default = null,
                                 $extra = null) {
        $this->name = $name;
        $this->extra = $extra;
        $this->length = $length;
        $this->nullable = $nullable;
        $this->type = $type;
        $this->unsigned = $unsigned;
        $this->default = $default;
    }

    /**
     * @return mixed
     */
    public function getName () {
        return $this->name;
    }

    /**
     * @return null
     */
    public function getDefault () {
        return $this->default;
    }

    /**
     * @return boolean
     */
    public function isUnsigned () {
        return $this->unsigned;
    }

    /**
     * @return ColumnType
     */
    public function getType () {
        return $this->type;
    }

    /**
     * @return boolean
     */
    public function isNullable () {
        return $this->nullable;
    }

    /**
     * @return null
     */
    public function getExtra () {
        return $this->extra;
    }
}