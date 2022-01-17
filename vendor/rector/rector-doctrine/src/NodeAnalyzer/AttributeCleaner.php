<?php

declare (strict_types=1);
namespace Rector\Doctrine\NodeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Attribute;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Property;
use Rector\NodeNameResolver\NodeNameResolver;
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
    public function __construct(\Rector\Doctrine\NodeAnalyzer\AttributeFinder $attributeFinder, \Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver)
    {
        $this->attributeFinder = $attributeFinder;
        $this->nodeNameResolver = $nodeNameResolver;
    }
    /**
     * @param \PhpParser\Node\Param|\PhpParser\Node\Stmt\ClassLike|\PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Property $node
     */
    public function clearAttributeAndArgName($node, string $attributeClass, string $argName) : void
    {
        $attribute = $this->attributeFinder->findAttributeByClass($node, $attributeClass);
        if (!$attribute instanceof \PhpParser\Node\Attribute) {
            return;
        }
        foreach ($attribute->args as $key => $arg) {
            if (!$arg->name instanceof \PhpParser\Node) {
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
