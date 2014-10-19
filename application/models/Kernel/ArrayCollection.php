<?php
/**
 * Application_Model_Kernel_ArrayCollection
 *
 * Manage content of one Field
 *
 * @author <oklosovich@gmail.com>
 * @package Kernel
 * @version 1.0
 */
class Application_Model_Kernel_ArrayCollection extends ArrayObject
{

    private $elements;

    public function __construct(array $elements)
    {
        parent::__construct($elements);
        $this->elements = $elements;
    }

    public function get($key)
    {
        return isset($this->elements[$key])?$this->elements[$key]:'';
    }

    public function getFText($key)
    {
        return isset($this->elements[$key])?$this->elements[$key]->getFieldText():'';
    }
}
