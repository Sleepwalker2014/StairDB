<?php

/**
 * Created by PhpStorm.
 * User: marcel
 * Date: 30.05.16
 * Time: 13:03
 */
class NumberPicker {
    /**
     * @var int
     */
    private $value;
    /**
     * @var int
     */
    private $minValue;
    /**
     * @var int
     */
    private $defaultValue;
    /**
     * @var int
     */
    private $maxValue;

    /**
     * NumberPicker constructor.
     *
     * @param int $value
     * @param int $minValue
     * @param int $maxValue
     * @param int $defaultValue
     */
    public function __construct ($value, $minValue, $maxValue, $defaultValue) {
        $this->value = $value;
        $this->minValue = $minValue;
        $this->defaultValue = $defaultValue;
        $this->maxValue = $maxValue;
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
                'minValue' => $this->minValue,
                'maxValue' => $this->maxValue,
                'defaultValue' => $this->defaultValue,
                'elementTemplate' => 'number.html'];
    }
}