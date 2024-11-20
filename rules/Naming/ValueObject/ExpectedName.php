<?php

declare (strict_types=1);
namespace Rector\Naming\ValueObject;

final class ExpectedName
{
    /**
     * @readonly
     */
    private string $name;
    /**
     * @readonly
     */
    private string $singularized;
    public function __construct(string $name, string $singularized)
    {
        $this->name = $name;
        $this->singularized = $singularized;
    }
    public function getName() : string
    {
        return $this->name;
    }
    public function getSingularized() : string
    {
        return $this->singularized;
    }
    public function isSingular() : bool
    {
        return $this->name === $this->singularized;
    }
}
