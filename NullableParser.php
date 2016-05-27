<?php

/**
 * Created by PhpStorm.
 * User: marcel
 * Date: 25.05.16
 * Time: 12:31
 */
class NullableParser {
    /**
     * @var bool $isNullable
     */
    private $isNullable;

    /**
     * NullableParser constructor.
     *
     * @param string $nullableString
     *
     * @throws Exception
     */
    public function __construct ($nullableString) {
        if (empty($nullableString) || ctype_space($nullableString) || is_object($nullableString)) {
            throw new Exception("empty string or non parsable object given.");
        }

        $this->isNullable = false;
        if ($nullableString === 'YES') {
            $this->isNullable = true;
        }
    }

    /**
     * @return bool $isNullable
     */
    public function isNullable () {
        return $this->isNullable;
    }
}