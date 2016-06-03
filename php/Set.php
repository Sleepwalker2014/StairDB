<?php

/**
 * Created by PhpStorm.
 * User: marcel
 * Date: 30.05.16
 * Time: 13:03
 */
class Set {
    /**
     * @var array|mixed[]
     */
    private $possibleValues;

    /**
     * Set constructor.
     *
     * @param string  $value
     * @param string  $defaultValue
     * @param mixed[] $possibleValues
     */
    public function __construct ($value, $defaultValue, array $possibleValues) {
        if (!in_array($value, $possibleValues) && !empty($value)) {
            throw new Exception("value is not possible.");
        }

        $this->value = $value;
        $this->defaultValue = $defaultValue;
        $this->possibleValues = $possibleValues;
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
                'possibleValues' => $this->possibleValues,
                'defaultValue' => $this->defaultValue,
                'elementTemplate' => 'set.html'];
    }
}