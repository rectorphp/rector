<?php

declare (strict_types=1);
namespace Rector\Laravel\NodeAnalyzer;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use PHPStan\Type\ObjectType;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\NodeTypeResolver;
final class LumenRouteRegisteringMethodAnalyzer
{
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\NodeTypeResolver
     */
    private $nodeTypeResolver;
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    public function __construct(NodeTypeResolver $nodeTypeResolver, NodeNameResolver $nodeNameResolver)
    {
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->nodeNameResolver = $nodeNameResolver;
    }
    public function isLumenRoutingClass(MethodCall $methodCall) : bool
    {
        return $this->nodeTypeResolver->isObjectType($methodCall->var, new ObjectType('Laravel\\Lumen\\Routing\\Router'));
    }
    /**
     * @param \PhpParser\Node\Identifier|\PhpParser\Node\Expr $name
     */
    public function isRoutesRegisterGroup($name) : bool
    {
        return $this->nodeNameResolver->isName($name, 'group');
    }
    /**
     * @param \PhpParser\Node\Identifier|\PhpParser\Node\Expr $name
     */
    public function isRoutesRegisterRoute($name) : bool
    {
        return $this->nodeNameResolver->isNames($name, ['delete', 'get', 'options', 'patch', 'post', 'put']);
    }
}
