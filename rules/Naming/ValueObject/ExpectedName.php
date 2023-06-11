<?php

declare (strict_types=1);
namespace Rector\Naming\ValueObject;

final class ExpectedName
{
    /**
     * @readonly
     * @var string
     */
    private $name;
    /**
     * @readonly
     * @var string
     */
    private $singularized;
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
