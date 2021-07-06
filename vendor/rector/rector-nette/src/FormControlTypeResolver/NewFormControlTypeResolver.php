<?php

declare (strict_types=1);
namespace Rector\Nette\FormControlTypeResolver;

use PhpParser\Node;
use PhpParser\Node\Expr\New_;
use Rector\Core\PhpParser\AstResolver;
use Rector\Core\ValueObject\MethodName;
use Rector\Nette\Contract\FormControlTypeResolverInterface;
use Rector\Nette\NodeResolver\MethodNamesByInputNamesResolver;
use Rector\NodeNameResolver\NodeNameResolver;
final class NewFormControlTypeResolver implements \Rector\Nette\Contract\FormControlTypeResolverInterface
{
    /**
     * @var \Rector\Nette\NodeResolver\MethodNamesByInputNamesResolver
     */
    private $methodNamesByInputNamesResolver;
    /**
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @var \Rector\Core\PhpParser\AstResolver
     */
    private $astResolver;
    public function __construct(\Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver, \Rector\Core\PhpParser\AstResolver $astResolver)
    {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->astResolver = $astResolver;
    }
    /**
     * @required
     */
    public function autowireNewFormControlTypeResolver(\Rector\Nette\NodeResolver\MethodNamesByInputNamesResolver $methodNamesByInputNamesResolver) : void
    {
        $this->methodNamesByInputNamesResolver = $methodNamesByInputNamesResolver;
    }
    /**
     * @return array<string, string>
     * @param \PhpParser\Node $node
     */
    public function resolve($node) : array
    {
        if (!$node instanceof \PhpParser\Node\Expr\New_) {
            return [];
        }
        $className = $this->nodeNameResolver->getName($node->class);
        if ($className === null) {
            return [];
        }
        $classMethod = $this->astResolver->resolveClassMethod($className, \Rector\Core\ValueObject\MethodName::CONSTRUCT);
        if ($classMethod === null) {
            return [];
        }
        return $this->methodNamesByInputNamesResolver->resolveExpr($classMethod);
    }
}
