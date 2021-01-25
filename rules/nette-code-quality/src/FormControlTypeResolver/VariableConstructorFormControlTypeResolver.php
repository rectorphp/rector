<?php

declare(strict_types=1);

namespace Rector\NetteCodeQuality\FormControlTypeResolver;

use PhpParser\Node;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Type\TypeWithClassName;
use Rector\Core\ValueObject\MethodName;
use Rector\NetteCodeQuality\Contract\FormControlTypeResolverInterface;
use Rector\NetteCodeQuality\Contract\MethodNamesByInputNamesResolverAwareInterface;
use Rector\NetteCodeQuality\NodeResolver\MethodNamesByInputNamesResolver;
use Rector\NodeCollector\NodeCollector\NodeRepository;
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
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    /**
     * @var NodeRepository
     */
    private $nodeRepository;

    public function __construct(
        NodeTypeResolver $nodeTypeResolver,
        NodeNameResolver $nodeNameResolver,
        NodeRepository $nodeRepository
    ) {
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->nodeRepository = $nodeRepository;
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

        $constructorClassMethod = $this->nodeRepository->findClassMethod(
            $formType->getClassName(),
            MethodName::CONSTRUCT
        );
        if (! $constructorClassMethod instanceof ClassMethod) {
            return [];
        }

        return $this->methodNamesByInputNamesResolver->resolveExpr($constructorClassMethod);
    }

    public function setResolver(MethodNamesByInputNamesResolver $methodNamesByInputNamesResolver): void
    {
        $this->methodNamesByInputNamesResolver = $methodNamesByInputNamesResolver;
    }
}
