<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\Laravel\NodeFactory;

use RectorPrefix20220606\PhpParser\Node\Expr;
use RectorPrefix20220606\PhpParser\Node\Expr\MethodCall;
use RectorPrefix20220606\PhpParser\Node\Expr\StaticCall;
use RectorPrefix20220606\PhpParser\Node\Identifier;
use RectorPrefix20220606\PHPStan\Type\ObjectType;
use RectorPrefix20220606\Rector\NodeNameResolver\NodeNameResolver;
use RectorPrefix20220606\Rector\NodeTypeResolver\NodeTypeResolver;
final class RouterRegisterNodeAnalyzer
{
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\NodeTypeResolver
     */
    private $nodeTypeResolver;
    public function __construct(NodeNameResolver $nodeNameResolver, NodeTypeResolver $nodeTypeResolver)
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
        if ($node instanceof MethodCall && $this->nodeTypeResolver->isObjectTypes($node->var, [new ObjectType('Illuminate\\Routing\\Router'), new ObjectType('Illuminate\\Routing\\RouteRegistrar')])) {
            return \true;
        }
        return $node instanceof StaticCall && $this->nodeNameResolver->isName($node->class, 'Illuminate\\Support\\Facades\\Route');
    }
    /**
     * @param \PhpParser\Node\Identifier|\PhpParser\Node\Expr $name
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
     * @param \PhpParser\Node\Identifier|\PhpParser\Node\Expr $name
     */
    public function isRegisterMultipleVerbs($name) : bool
    {
        return $this->nodeNameResolver->isName($name, 'match');
    }
    /**
     * @param \PhpParser\Node\Identifier|\PhpParser\Node\Expr $name
     */
    public function isRegisterAllVerbs($name) : bool
    {
        return $this->nodeNameResolver->isName($name, 'any');
    }
    /**
     * @param \PhpParser\Node\Identifier|\PhpParser\Node\Expr $name
     */
    public function isRegisterAnyVerb($name) : bool
    {
        return $this->nodeNameResolver->isNames($name, ['delete', 'get', 'options', 'patch', 'post', 'put']);
    }
    /**
     * @param \PhpParser\Node\Identifier|\PhpParser\Node\Expr $name
     */
    public function isRegisterFallback($name) : bool
    {
        return $this->nodeNameResolver->isName($name, 'fallback');
    }
}
