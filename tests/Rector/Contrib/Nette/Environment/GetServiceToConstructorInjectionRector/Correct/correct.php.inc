<?php declare (strict_types=1);

class SomePresenter
{
    /**
     * @var \someType
     */
    private $someType;
    public function __construct(\someType $someType)
    {
        $this->someType = $someType;
    }
    public function render()
    {
        $someService = $this->someType;
        $someResult = $this->someType->callMe();
    }
}
