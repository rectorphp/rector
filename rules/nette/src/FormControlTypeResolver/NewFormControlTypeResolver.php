<?php

declare(strict_types=1);

namespace Rector\Nette\FormControlTypeResolver;

use PhpParser\Node;
use PhpParser\Node\Expr\New_;
use Rector\Nette\Contract\FormControlTypeResolverInterface;
use Rector\Nette\Contract\MethodNamesByInputNamesResolverAwareInterface;
use Rector\Nette\NodeResolver\MethodNamesByInputNamesResolver;
use Rector\Nette\ValueObject\MethodName;
use Rector\NodeCollector\NodeFinder\FunctionLikeParsedNodesFinder;
use Rector\NodeNameResolver\NodeNameResolver;

final class NewFormControlTypeResolver implements FormControlTypeResolverInterface, MethodNamesByInputNamesResolverAwareInterface
{
    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    /**
     * @var FunctionLikeParsedNodesFinder
     */
    private $functionLikeParsedNodesFinder;

    /**
     * @var MethodNamesByInputNamesResolver
     */
    private $methodNamesByInputNamesResolver;

    public function __construct(
        NodeNameResolver $nodeNameResolver,
        FunctionLikeParsedNodesFinder $functionLikeParsedNodesFinder
    ) {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->functionLikeParsedNodesFinder = $functionLikeParsedNodesFinder;
    }

    /**
     * @return array<string, string>
     */
    public function resolve(Node $node): array
    {
        if (! $node instanceof New_) {
            return [];
        }

        $className = $this->nodeNameResolver->getName($node->class);
        if ($className === null) {
            return [];
        }

        $constructorClassMethod = $this->functionLikeParsedNodesFinder->findClassMethod(
            MethodName::CONSTRUCT,
            $className
        );
        if ($constructorClassMethod === null) {
            return [];
        }

        return $this->methodNamesByInputNamesResolver->resolveExpr($constructorClassMethod);
    }

    public function setResolver(MethodNamesByInputNamesResolver $methodNamesByInputNamesResolver): void
    {
        $this->methodNamesByInputNamesResolver = $methodNamesByInputNamesResolver;
    }
}
