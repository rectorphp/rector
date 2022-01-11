<?php

declare (strict_types=1);
namespace Rector\Doctrine\NodeAnalyzer;

use PhpParser\Node\Attribute;
use PhpParser\Node\Expr;
use Rector\NodeNameResolver\NodeNameResolver;
final class AttributeArgValueResolver
{
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    public function __construct(\Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver)
    {
        $this->nodeNameResolver = $nodeNameResolver;
    }
    /**
     * @return \PhpParser\Node\Expr|null
     */
    public function resolve(\PhpParser\Node\Attribute $attribute, string $argName)
    {
        foreach ($attribute->args as $arg) {
            if ($arg->name === null) {
                continue;
            }
            if (!$this->nodeNameResolver->isName($arg->name, $argName)) {
                continue;
            }
            return $arg->value;
        }
        return null;
    }
}
