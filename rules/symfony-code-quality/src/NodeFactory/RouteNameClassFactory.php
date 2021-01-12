<?php

declare(strict_types=1);

namespace Rector\SymfonyCodeQuality\NodeFactory;

use PhpParser\Node\Stmt\Namespace_;
use Rector\Core\PhpParser\Node\NodeFactory;
use Rector\SymfonyCodeQuality\ValueObject\ClassName;
use Rector\SymfonyCodeQuality\ValueObject\ConstantNameAndValue;
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
     * @param ConstantNameAndValue[] $constantNamesAndValues
     */
    public function create(array $constantNamesAndValues): Namespace_
    {
        $classBuilder = new ClassBuilder(ClassName::ROUTE_CLASS_SHORT_NAME);
        $classBuilder->makeFinal();

        foreach ($constantNamesAndValues as $constantNameAndValue) {
            $classConst = $this->nodeFactory->createPublicClassConst(
                $constantNameAndValue->getName(),
                $constantNameAndValue->getValue()
            );
            $classBuilder->addStmt($classConst);
        }

        $namespaceBuilder = new NamespaceBuilder(ClassName::ROUTE_NAME_NAMESPACE);
        $namespaceBuilder->addStmt($classBuilder->getNode());

        return $namespaceBuilder->getNode();
    }
}
