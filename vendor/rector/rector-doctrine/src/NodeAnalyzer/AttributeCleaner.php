<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\Doctrine\NodeAnalyzer;

use RectorPrefix20220606\PhpParser\Node;
use RectorPrefix20220606\PhpParser\Node\Attribute;
use RectorPrefix20220606\PhpParser\Node\Param;
use RectorPrefix20220606\PhpParser\Node\Stmt\ClassLike;
use RectorPrefix20220606\PhpParser\Node\Stmt\ClassMethod;
use RectorPrefix20220606\PhpParser\Node\Stmt\Property;
use RectorPrefix20220606\Rector\NodeNameResolver\NodeNameResolver;
final class AttributeCleaner
{
    /**
     * @readonly
     * @var \Rector\Doctrine\NodeAnalyzer\AttributeFinder
     */
    private $attributeFinder;
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    public function __construct(AttributeFinder $attributeFinder, NodeNameResolver $nodeNameResolver)
    {
        $this->attributeFinder = $attributeFinder;
        $this->nodeNameResolver = $nodeNameResolver;
    }
    /**
     * @param \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Property|\PhpParser\Node\Stmt\ClassLike|\PhpParser\Node\Param $node
     */
    public function clearAttributeAndArgName($node, string $attributeClass, string $argName) : void
    {
        $attribute = $this->attributeFinder->findAttributeByClass($node, $attributeClass);
        if (!$attribute instanceof Attribute) {
            return;
        }
        foreach ($attribute->args as $key => $arg) {
            if (!$arg->name instanceof Node) {
                continue;
            }
            if (!$this->nodeNameResolver->isName($arg->name, $argName)) {
                continue;
            }
            // remove attribute
            unset($attribute->args[$key]);
        }
    }
}
