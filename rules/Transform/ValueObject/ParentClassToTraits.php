<?php

declare (strict_types=1);
namespace Rector\Transform\ValueObject;

use Rector\Validation\RectorAssert;
use RectorPrefix202411\Webmozart\Assert\Assert;
final class ParentClassToTraits
{
    /**
     * @readonly
     * @var string
     */
    private $parentType;
    /**
     * @var string[]
     * @readonly
     */
    private $traitNames;
    /**
     * @param string[] $traitNames
     */
    public function __construct(string $parentType, array $traitNames)
    {
        $this->parentType = $parentType;
        $this->traitNames = $traitNames;
        RectorAssert::className($parentType);
        Assert::allString($traitNames);
    }
    public function getParentType() : string
    {
        return $this->parentType;
    }
    /**
     * @return string[]
     */
    public function getTraitNames() : array
    {
        // keep the Trait order the way it is in config
        return \array_reverse($this->traitNames);
    }
}
