<?php

declare(strict_types=1);

namespace Rector\NetteCodeQuality\FormControlTypeResolver;

use PhpParser\Node;
use PhpParser\Node\Expr\Variable;
use PHPStan\Type\TypeWithClassName;
use Rector\Core\ValueObject\MethodName;
use Rector\NetteCodeQuality\Contract\FormControlTypeResolverInterface;
use Rector\NetteCodeQuality\Contract\MethodNamesByInputNamesResolverAwareInterface;
use Rector\NetteCodeQuality\NodeResolver\MethodNamesByInputNamesResolver;
use Rector\NodeCollector\NodeFinder\FunctionLikeParsedNodesFinder;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\NodeTypeResolver;

final class VariableConstructorFormControlTypeResolver implements FormControlTypeResolverInterface, MethodNamesByInputNamesResolverAwareInterface
{
    /**
     * @var MethodNamesByInputNamesResolver
     */
    private $methodNamesByInputNamesResolver;

    /**
     * @var NodeTypeResolver
     */
    private $nodeTypeResolver;

    /**
     * @var FunctionLikeParsedNodesFinder
     */
    private $functionLikeParsedNodesFinder;

    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    public function __construct(
        NodeTypeResolver $nodeTypeResolver,
        NodeNameResolver $nodeNameResolver,
        FunctionLikeParsedNodesFinder $functionLikeParsedNodesFinder
    ) {
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->functionLikeParsedNodesFinder = $functionLikeParsedNodesFinder;
        $this->nodeNameResolver = $nodeNameResolver;
    }

    /**
     * @return array<string, string>
     */
    public function resolve(Node $node): array
    {
        if (! $node instanceof Variable) {
            return [];
        }

        // handled else-where
        if ($this->nodeNameResolver->isName($node, 'this')) {
            return [];
        }

        $formType = $this->nodeTypeResolver->getStaticType($node);
        if (! $formType instanceof TypeWithClassName) {
            return [];
        }

        if (! is_a($formType->getClassName(), 'Nette\Application\UI\Form', true)) {
            return [];
        }

        $constructorClassMethod = $this->functionLikeParsedNodesFinder->findClassMethod(
            MethodName::CONSTRUCT,
            $formType->getClassName()
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
