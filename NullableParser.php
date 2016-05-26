<?php

/**
 * Created by PhpStorm.
 * User: marcel
 * Date: 25.05.16
 * Time: 12:31
 */
class ColumnTypeParser {

    /**
     * @var ColumnType type
     */
    private $type;

    /**
     * @var int|null length
     */
    private $length = null;

    /**
     * @var bool unsigned
     */
    private $unsigned = false;

    /**
     * @var bool zerofill
     */
    private $zerofill = false;

    /**
     * ColumnTypeParser constructor.
     *
     * @param $typeString
     */
    public function __construct ($typeString) {
        if (empty($typeString) || ctype_space($typeString) || is_object($typeString)) {
            throw new Exception("empty string or non parsable object given.");
        }

        $parsedType = explode(' ', $typeString);

        $startBrace = strpos($parsedType[0], "(");
        $endBrace = strpos($parsedType[0], ")");
        if ((!$startBrace || !$endBrace) && $parsedType[0] != 'date') {
            throw new Exception("string is not parseable.");
        }

        $this->createTypeFromString($parsedType[0], $startBrace);

        if (isset($parsedType[1])) {
            $this->parseOutZerofillAndUnsignedFromString($parsedType[1]);
        }

        if (isset($parsedType[2])) {
            $this->parseOutZerofillAndUnsignedFromString($parsedType[2]);
        }

        if ($parsedType[0] != 'date') {
            $this->length = substr($parsedType[0], $startBrace + 1, ($endBrace - $startBrace) - 1);
        }
    }

    private function createTypeFromString ($parseString, $bracePosition) {
        $typeName = $parseString;

        if ($bracePosition) {
            $typeName = substr($parseString, 0, $bracePosition);
        }

        $this->type = new ColumnType($typeName);
    }

    private function parseOutZerofillAndUnsignedFromString ($parseString) {
        if ($parseString === 'unsigned') {
            $this->unsigned = true;
        }

        if ($parseString === 'zerofill') {
            $this->zerofill = true;
        }
    }

    /**
     * @return ColumnType
     */
    public function getType () {
        return $this->type;
    }

    /**
     * @return int|null
     */
    public function getLength () {
        return $this->length;
    }

    /**
     * @return bool
     */
    public function isUnsigned () {
        return $this->unsigned;
    }

    /**
     * @return bool
     */
    public function useZerofill () {
        return $this->zerofill;
    }
}