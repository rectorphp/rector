<?php

declare(strict_types=1);

namespace Rector\NetteToSymfony\NodeFactory;

use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Namespace_;
use PHPStan\Analyser\Scope;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;

final class SymfonyControllerFactory
{
    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    /**
     * @var ActionWithFormProcessClassMethodFactory
     */
    private $actionWithFormProcessClassMethodFactory;

    public function __construct(
        NodeNameResolver $nodeNameResolver,
        ActionWithFormProcessClassMethodFactory $actionWithFormProcessClassMethodFactory
    ) {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->actionWithFormProcessClassMethodFactory = $actionWithFormProcessClassMethodFactory;
    }

    public function createNamespace(Class_ $node, Class_ $formTypeClass): ?Namespace_
    {
        $scope = $node->getAttribute(AttributeKey::SCOPE);
        if (! $scope instanceof Scope) {
            return null;
        }

        /** @var string $namespaceName */
        $namespaceName = $scope->getNamespace();

        $formControllerClass = new Class_('SomeFormController');
        $formControllerClass->extends = new FullyQualified(
            'Symfony\Bundle\FrameworkBundle\Controller\AbstractController'
        );

        $formTypeClass = $namespaceName . '\\' . $this->nodeNameResolver->getName($formTypeClass);
        $formControllerClass->stmts[] = $this->actionWithFormProcessClassMethodFactory->create($formTypeClass);

        $namespace = new Namespace_(new Name($namespaceName));
        $namespace->stmts[] = $formControllerClass;

        return $namespace;
    }
}
