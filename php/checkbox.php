<?php

/**
 * Created by PhpStorm.
 * User: marcel
 * Date: 30.05.16
 * Time: 13:03
 */
class checkbox {
    /**
     * @var bool|false
     */
    private $value;
    /**
     * @var bool
     */
    private $defaultValue;

    /**
     * checkbox constructor.
     *
     * @param bool|false $value
     * @param bool       $defaultValue
     */
    public function __construct ($value = false, $defaultValue) {
        $this->value = $value;
        $this->defaultValue = $defaultValue;
    }

    /**
     * @return bool|false
     */
    public function getValue () {
        if ($this->value === 'true' || $this->value === 'ON') {
            return true;
        }

        return false;
    }

    /**
     * @return bool|false
     */
    public function getHTMLOutput () {
        return ['value' => $this->getValue(),
                'elementTemplate' => 'checkbox.html'];
    }
}