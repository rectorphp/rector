<?php

declare (strict_types=1);
namespace Rector\Doctrine\NodeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Attribute;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Property;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\NodeNameResolver\NodeNameResolver;
/**
 * @api
 */
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
    public function __construct(\Rector\Doctrine\NodeAnalyzer\AttributeFinder $attributeFinder, NodeNameResolver $nodeNameResolver)
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
            throw new ShouldNotHappenException();
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
