<?php

declare (strict_types=1);
namespace RectorPrefix20220325\Idiosyncratic\EditorConfig\Declaration;

final class UnsetDeclaration extends \RectorPrefix20220325\Idiosyncratic\EditorConfig\Declaration\Declaration
{
    public function __construct(string $name)
    {
        $this->setName($name);
        parent::__construct('unset');
    }
    /**
     * @return mixed
     */
    protected function getTypedValue(string $value)
    {
        return null;
    }
}
