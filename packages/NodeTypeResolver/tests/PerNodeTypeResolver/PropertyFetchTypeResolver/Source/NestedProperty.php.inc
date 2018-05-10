<?php declare(strict_types=1);

namespace Roave\BetterReflection\NodeCompiler;

use Rector\NodeTypeResolver\Tests\Source\NestedProperty\ClassWithPropertyLevel1;

class SomeClassWithNestedPublicProperties
{
    /**
     * @var ClassWithPropertyLevel1
     */
    private $level1;

    /**
     * Get the name of the property.
     *
     * @return string
     */
    public function getName() : string
    {
        return $this->level1->level2s[5]->level3->someMethod();
    }
}
