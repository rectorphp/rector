<?php

namespace Rector\Tests\TypeDeclaration\Rector\Property\TypedPropertyFromStrictConstructorRector\FixturePhp73;

trait ConstructInTrait
{
    /**
     * @var string
     */
    private $column;

    /**
     * @param string|null $column
     */
    public function __construct(string $property, $column = null)
    {
        $this->column = $column ?? $property;
    }
}
