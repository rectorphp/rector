<?php

declare(strict_types=1);

namespace Rector\SymfonyCodeQuality\NodeFactory;

use PhpParser\Node\Stmt\Namespace_;
use Rector\Core\PhpParser\Node\NodeFactory;
use Rector\SymfonyCodeQuality\Composer\ComposerNamespaceMatcher;
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

    /**
     * @var ComposerNamespaceMatcher
     */
    private $composerNamespaceMatcher;

    public function __construct(NodeFactory $nodeFactory, ComposerNamespaceMatcher $composerNamespaceMatcher)
    {
        $this->nodeFactory = $nodeFactory;
        $this->composerNamespaceMatcher = $composerNamespaceMatcher;
    }

    /**
     * @param ConstantNameAndValue[] $constantNamesAndValues
     */
    public function create(array $constantNamesAndValues, string $fileLocation): Namespace_
    {
        $classBuilder = new ClassBuilder(ClassName::ROUTE_CLASS_SHORT_NAME);
        $classBuilder->makeFinal();

        $namespaceName = $this->composerNamespaceMatcher->matchNamespaceForLocation($fileLocation);
        if ($namespaceName === null) {
            $namespaceName = ClassName::ROUTE_NAME_NAMESPACE;
        } else {
            $namespaceName .= '\\ValueObject\\Routing';
        }

        foreach ($constantNamesAndValues as $constantNameAndValue) {
            $classConst = $this->nodeFactory->createPublicClassConst(
                $constantNameAndValue->getName(),
                $constantNameAndValue->getValue()
            );
            $classBuilder->addStmt($classConst);
        }

        $namespaceBuilder = new NamespaceBuilder($namespaceName);
        $namespaceBuilder->addStmt($classBuilder->getNode());

        return $namespaceBuilder->getNode();
    }
}
