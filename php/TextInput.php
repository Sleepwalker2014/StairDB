<?php

/**
 * Created by PhpStorm.
 * User: marcel
 * Date: 30.05.16
 * Time: 13:03
 */
class TextInput {
    /**
     * @var int
     */
    private $value;
    /**
     * @var int
     */
    private $maxValue;

    /**
     * TextInput constructor.
     *
     * @param int $value
     * @param string $defaultValue
     */
    public function __construct ($value,$defaultValue) {
        $this->value = $value;
        $this->defaultValue = $defaultValue;
    }

    /**
     * @return int
     */
    public function getValue () {
        return $this->value;
    }

    /**
     * @return mixed[]
     */
    public function getHTMLOutput () {
        return ['value' => $this->value,
                'defaultValue' => $this->defaultValue,
                'elementTemplate' => 'textInput.html'];
    }
}