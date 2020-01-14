<?php

declare(strict_types=1);

namespace Rector\Utils\PHPStanAttributeTypeSyncer\NodeFactory;

use Nette\Utils\Strings;
use PhpParser\Builder\Class_;
use PhpParser\BuilderFactory;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Namespace_;
use Rector\BetterPhpDocParser\Attributes\Attribute\AttributeTrait;
use Rector\BetterPhpDocParser\Contract\PhpDocNode\AttributeAwareNodeInterface;
use Rector\Utils\PHPStanAttributeTypeSyncer\ClassNaming\AttributeClassNaming;
use Rector\Utils\PHPStanAttributeTypeSyncer\ValueObject\Paths;

final class AttributeAwareClassFactory
{
    /**
     * @var BuilderFactory
     */
    private $builderFactory;

    /**
     * @var AttributeClassNaming
     */
    private $attributeClassNaming;

    public function __construct(BuilderFactory $builderFactory, AttributeClassNaming $attributeClassNaming)
    {
        $this->builderFactory = $builderFactory;
        $this->attributeClassNaming = $attributeClassNaming;
    }

    public function createFromPhpDocParserNodeClass(string $nodeClass): Namespace_
    {
        if (Strings::contains($nodeClass, '\\Type\\')) {
            $namespace = Paths::NAMESPACE_TYPE_NODE;
        } else {
            $namespace = Paths::NAMESPACE_PHPDOC_NODE;
        }

        $namespaceBuilder = $this->builderFactory->namespace($namespace);

        $shortClassName = $this->attributeClassNaming->createAttributeAwareShortClassName($nodeClass);
        $classBuilder = $this->createClassBuilder($nodeClass, $shortClassName);

        $useTrait = $this->builderFactory->useTrait(new FullyQualified(AttributeTrait::class));
        $classBuilder->addStmt($useTrait);

        $namespaceBuilder->addStmt($classBuilder->getNode());

        return $namespaceBuilder->getNode();
    }

    private function createClassBuilder(string $nodeClass, string $shortClassName): Class_
    {
        $classBuilder = $this->builderFactory->class($shortClassName);
        $classBuilder->makeFinal();
        $classBuilder->extend(new FullyQualified($nodeClass));
        $classBuilder->implement(new FullyQualified(AttributeAwareNodeInterface::class));

        return $classBuilder;
    }
}
