<?php

declare (strict_types=1);
namespace Rector\Laravel\NodeFactory;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use PHPStan\Type\ObjectType;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\NodeTypeResolver;
final class RouterRegisterNodeAnalyzer
{
    /**
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @var \Rector\NodeTypeResolver\NodeTypeResolver
     */
    private $nodeTypeResolver;
    public function __construct(\Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver, \Rector\NodeTypeResolver\NodeTypeResolver $nodeTypeResolver)
    {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->nodeTypeResolver = $nodeTypeResolver;
    }
    /**
     * @param \PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\StaticCall $node
     */
    public function isRegisterMethodStaticCall($node) : bool
    {
        if (!$this->isRegisterName($node->name)) {
            return \false;
        }
        if ($node instanceof \PhpParser\Node\Expr\MethodCall && $this->nodeTypeResolver->isObjectTypes($node->var, [new \PHPStan\Type\ObjectType('Illuminate\\Routing\\Router'), new \PHPStan\Type\ObjectType('Illuminate\\Routing\\RouteRegistrar')])) {
            return \true;
        }
        return $node instanceof \PhpParser\Node\Expr\StaticCall && $this->nodeNameResolver->isName($node->class, 'Illuminate\\Support\\Facades\\Route');
    }
    /**
     * @param \PhpParser\Node\Expr|\PhpParser\Node\Identifier $name
     */
    public function isRegisterName($name) : bool
    {
        if ($this->isRegisterAnyVerb($name)) {
            return \true;
        }
        if ($this->isRegisterMultipleVerbs($name)) {
            return \true;
        }
        if ($this->isRegisterAllVerbs($name)) {
            return \true;
        }
        return $this->isRegisterFallback($name);
    }
    /**
     * @param \PhpParser\Node\Expr|\PhpParser\Node\Identifier $name
     */
    public function isRegisterMultipleVerbs($name) : bool
    {
        return $this->nodeNameResolver->isName($name, 'match');
    }
    /**
     * @param \PhpParser\Node\Expr|\PhpParser\Node\Identifier $name
     */
    public function isRegisterAllVerbs($name) : bool
    {
        return $this->nodeNameResolver->isName($name, 'any');
    }
    /**
     * @param \PhpParser\Node\Expr|\PhpParser\Node\Identifier $name
     */
    public function isRegisterAnyVerb($name) : bool
    {
        return $this->nodeNameResolver->isNames($name, ['delete', 'get', 'options', 'patch', 'post', 'put']);
    }
    /**
     * @param \PhpParser\Node\Expr|\PhpParser\Node\Identifier $name
     */
    public function isRegisterFallback($name) : bool
    {
        return $this->nodeNameResolver->isName($name, 'fallback');
    }
}
