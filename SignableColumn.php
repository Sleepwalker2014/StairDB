<?php

/**
 * Created by PhpStorm.
 * User: marcel
 * Date: 26.05.16
 * Time: 12:45
 */
class GeneralColumn {
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
                                 $nullable = false,
                                 $default = null,
                                 $extra = null) {
        $this->name = $name;
        $this->extra = $extra;
        $this->type = $type;
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
     * @return ColumnType
     */
    public function getType () {
        return $this->type;
    }

    /**
     * @return null
     */
    public function getExtra () {
        return $this->extra;
    }
}