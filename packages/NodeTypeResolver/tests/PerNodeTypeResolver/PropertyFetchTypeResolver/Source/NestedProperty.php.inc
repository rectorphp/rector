<?php declare(strict_types=1);

namespace Rector\BetterReflection\NodeCompiler;

//use PhpParser\Node\Stmt\Property as PropertyNode;
use Rector\NodeTypeResolver\Tests\Source\NestedProperty\ClassWithPropertyLevel1;

//class someClass
//{
//    /**
//     * @var PropertyNode
//     */
//    private $node;
//
//    /**
//     * Get the name of the property.
//     *
//     * @return string
//     */
//    public function getName() : string
//    {
//        return $this->node->props[5]->name->toString();
//    }
//}


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
