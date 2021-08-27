<?php

declare (strict_types=1);
namespace RectorPrefix20210827\Idiosyncratic\EditorConfig\Declaration;

final class UnsetDeclaration extends \RectorPrefix20210827\Idiosyncratic\EditorConfig\Declaration\Declaration
{
    public function __construct(string $name)
    {
        $this->setName($name);
        parent::__construct('unset');
    }
    /**
     * @return mixed
     * @param string $value
     */
    protected function getTypedValue($value)
    {
        return null;
    }
}
