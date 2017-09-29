<?php declare (strict_types=1);

class SomeClass
{
    use \Nette\SmartObject;

    public function getValue()
    {
        return $this->value;
    }

}
