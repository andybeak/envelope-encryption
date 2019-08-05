<?php namespace AndyBeak\EnvelopeEncryption\Enum;

abstract class BaseEnum
{
    /**
     * BaseEnum constructor.
     * @param $value
     * @throws \ReflectionException
     */
    final public function __construct($value)
    {
        $c = new \ReflectionClass($this);
        if (!in_array($value, $c->getConstants())) {
            throw \IllegalArgumentException();
        }
        $this->value = $value;
    }
    /**
     * @return mixed
     */
    final public function __toString()
    {
        return $this->value;
    }
}