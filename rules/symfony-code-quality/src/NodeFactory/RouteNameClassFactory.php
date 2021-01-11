<?php

declare(strict_types=1);

namespace Rector\SymfonyCodeQuality\NodeFactory;

use PhpParser\Node\Stmt\Namespace_;
use Rector\Core\PhpParser\Node\NodeFactory;
use Rector\SymfonyCodeQuality\ValueObject\ClassName;
use Symplify\Astral\ValueObject\NodeBuilder\ClassBuilder;
use Symplify\Astral\ValueObject\NodeBuilder\NamespaceBuilder;

final class RouteNameClassFactory
{
    /**
     * @var NodeFactory
     */
    private $nodeFactory;

    public function __construct(NodeFactory $nodeFactory)
    {
        $this->nodeFactory = $nodeFactory;
    }

    /**
     * @param array<string, string> $constantNamesToValues
     */
    public function create(array $constantNamesToValues): Namespace_
    {
        $classBuilder = new ClassBuilder(ClassName::ROUTE_CLASS_SHORT_NAME);
        $classBuilder->makeFinal();

        foreach ($constantNamesToValues as $constantName => $constantValue) {
            $classConst = $this->nodeFactory->createPublicClassConst($constantName, $constantValue);
            $classBuilder->addStmt($classConst);
        }

        $namespaceBuilder = new NamespaceBuilder(ClassName::ROUTE_NAME_NAMESPACE);
        $namespaceBuilder->addStmt($classBuilder->getNode());

        return $namespaceBuilder->getNode();
    }
}
