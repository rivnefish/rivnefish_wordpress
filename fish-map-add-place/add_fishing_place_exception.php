<?php
/**
 * Exception class that return ID of wrong element
 *
 * @author ygrabar
 */

class IDException extends Exception
{
    // Extend basic Exception class with ID of the element
    // that didn't pass the check and returned exception
    protected $id = null;

    public function __construct($message, $id = null)
    {
        parent::__construct($message);

        if (!is_null($id))
        {
            $this->id = $id;
        }
    }

    public function getId() {
        return $this->id;
    }
}
