<?php

/**
 * Created by PhpStorm.
 * User: marcel
 * Date: 25.05.16
 * Time: 15:11
 */
class ColumnType {
    /**
     *
     */
    const INT = 'int';
    /**
     *
     */
    const DATE = 'date';
    /**
     *
     */
    const VARCHAR = 'varchar';
    /**
     * @var string $type
     */
    private $type;

    /**
     * ColumnType constructor.
     *
     * @param string $type
     *
     * @throws Exception
     */
    public function __construct ($type) {
        if ($type !== self::INT &&
            $type !== self::DATE &&
            $type !== self::VARCHAR
        ) {
            throw new Exception($type.' is no valid column type');
        }

        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getType () {
        return $this->type;
    }
}