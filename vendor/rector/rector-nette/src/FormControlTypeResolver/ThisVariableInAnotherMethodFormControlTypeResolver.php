<?php

declare (strict_types=1);
namespace Rector\Nette\FormControlTypeResolver;

use PhpParser\Node;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Core\ValueObject\MethodName;
use Rector\Nette\Contract\FormControlTypeResolverInterface;
use Rector\Nette\NodeResolver\MethodNamesByInputNamesResolver;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
final class ThisVariableInAnotherMethodFormControlTypeResolver implements \Rector\Nette\Contract\FormControlTypeResolverInterface
{
    /**
     * @var \Rector\Nette\NodeResolver\MethodNamesByInputNamesResolver
     */
    private $methodNamesByInputNamesResolver;
    /**
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    public function __construct(\Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver)
    {
        $this->nodeNameResolver = $nodeNameResolver;
    }
    /**
     * @required
     */
    public function autowireThisVariableInAnotherMethodFormControlTypeResolver(\Rector\Nette\NodeResolver\MethodNamesByInputNamesResolver $methodNamesByInputNamesResolver) : void
    {
        $this->methodNamesByInputNamesResolver = $methodNamesByInputNamesResolver;
    }
    /**
     * @return array<string, string>
     * @param \PhpParser\Node $node
     */
    public function resolve($node) : array
    {
        if (!$node instanceof \PhpParser\Node\Expr\Variable) {
            return [];
        }
        $classMethod = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::METHOD_NODE);
        if (!$classMethod instanceof \PhpParser\Node\Stmt\ClassMethod) {
            return [];
        }
        // handled elsewhere
        if ($this->nodeNameResolver->isName($classMethod, \Rector\Core\ValueObject\MethodName::CONSTRUCT)) {
            return [];
        }
        $classLike = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::CLASS_NODE);
        if (!$classLike instanceof \PhpParser\Node\Stmt\Class_) {
            return [];
        }
        $constructorClassMethod = $classLike->getMethod(\Rector\Core\ValueObject\MethodName::CONSTRUCT);
        if ($constructorClassMethod === null) {
            return [];
        }
        return $this->methodNamesByInputNamesResolver->resolveExpr($constructorClassMethod);
    }
}
