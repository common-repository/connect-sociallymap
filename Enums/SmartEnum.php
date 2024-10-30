<?php

namespace SociallymapConnect\Enums;

use SociallymapConnect\Includes\Exceptions\SmartEnumException;

class SmartEnum
{
    /** @var mixed */
    protected $currentValue;

    /** @var array */
    protected static $poolValues = [];

    /**
     * SmartEnum constructor.
     * @param $currentValue
     * @throws SmartEnumException
     * @throws \ReflectionException
     */
    public function __construct($currentValue)
    {
        if (!self::isValid($currentValue)) {
            throw new SmartEnumException($currentValue);
        }

        $this->currentValue = $currentValue;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string)$this->getValue();
    }

    /**
     * @param $value
     * @return bool
     * @throws \ReflectionException
     */
    public static function isValid($value)
    {
        return in_array($value, self::getValues());
    }

    /**
     * @return mixed
     * @throws \ReflectionException
     */
    public static function getValues()
    {
        $calledClass = static::class;

        if (!isset(self::$poolValues[$calledClass])) {
            $reflectionClass = new \ReflectionClass(static::class);
            self::$poolValues[$calledClass] = $reflectionClass->getConstants();
        }

        return self::$poolValues[$calledClass];
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->currentValue;
    }
}
