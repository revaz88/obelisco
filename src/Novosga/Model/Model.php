<?php

namespace Novosga\Model;

/**
 * Modelo abstrato.
 *
 * 
 */
abstract class Model
{
    /**
     * @return String
     */
    public function toString()
    {
        return get_class($this);
    }

    /**
     * @return String
     */
    public function __tostring()
    {
        return $this->toString();
    }
}
