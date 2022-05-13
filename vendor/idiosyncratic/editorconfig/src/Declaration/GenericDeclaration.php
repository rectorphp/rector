<?php

declare (strict_types=1);
namespace RectorPrefix20220513\Idiosyncratic\EditorConfig\Declaration;

final class GenericDeclaration extends \RectorPrefix20220513\Idiosyncratic\EditorConfig\Declaration\Declaration
{
    public function __construct(string $name, string $value)
    {
        $this->setName($name);
        parent::__construct($value);
    }
}
