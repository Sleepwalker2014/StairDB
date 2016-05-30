<?php

/**
 * Created by PhpStorm.
 * User: marcel
 * Date: 30.05.16
 * Time: 13:03
 */
class DirectoryPicker {
    /**
     * @var string
     */
    private $value;

    /**
     * DirectoryPicker constructor.
     *
     * @param int $value
     */
    public function __construct ($value = null) {
        $this->value = $value;
    }

    /**
     * @return string
     */
    public function getValue () {
        return $this->value;
    }

    /**
     * @return mixed[]
     */
    public function getHTMLOutput () {
        return ['value' => $this->value,
                'elementTemplate' => 'DirectoryPicker.html'];
    }
}