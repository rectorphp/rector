<?php

declare(strict_types=1);

namespace Rector\NetteToSymfony\NodeFactory;

use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Namespace_;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\SmartFileSystem\SmartFileInfo;

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
        $fileInfo = $node->getAttribute(AttributeKey::FILE_INFO);
        if (! $fileInfo instanceof SmartFileInfo) {
            return null;
        }

        /** @var string $namespaceName */
        $namespaceName = $node->getAttribute(AttributeKey::NAMESPACE_NAME);

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
