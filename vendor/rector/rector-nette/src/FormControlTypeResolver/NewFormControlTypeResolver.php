<?php

declare (strict_types=1);
namespace Rector\Nette\FormControlTypeResolver;

use PhpParser\Node;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Core\PhpParser\AstResolver;
use Rector\Core\ValueObject\MethodName;
use Rector\Nette\Contract\FormControlTypeResolverInterface;
use Rector\Nette\NodeResolver\MethodNamesByInputNamesResolver;
use Rector\NodeNameResolver\NodeNameResolver;
use RectorPrefix202208\Symfony\Contracts\Service\Attribute\Required;
final class NewFormControlTypeResolver implements FormControlTypeResolverInterface
{
    /**
     * @var \Rector\Nette\NodeResolver\MethodNamesByInputNamesResolver
     */
    private $methodNamesByInputNamesResolver;
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\AstResolver
     */
    private $astResolver;
    public function __construct(NodeNameResolver $nodeNameResolver, AstResolver $astResolver)
    {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->astResolver = $astResolver;
    }
    /**
     * @required
     */
    public function autowire(MethodNamesByInputNamesResolver $methodNamesByInputNamesResolver) : void
    {
        $this->methodNamesByInputNamesResolver = $methodNamesByInputNamesResolver;
    }
    /**
     * @return array<string, string>
     */
    public function resolve(Node $node) : array
    {
        if (!$node instanceof New_) {
            return [];
        }
        $className = $this->nodeNameResolver->getName($node->class);
        if ($className === null) {
            return [];
        }
        $classMethod = $this->astResolver->resolveClassMethod($className, MethodName::CONSTRUCT);
        if (!$classMethod instanceof ClassMethod) {
            return [];
        }
        return $this->methodNamesByInputNamesResolver->resolveExpr($classMethod);
    }
}
