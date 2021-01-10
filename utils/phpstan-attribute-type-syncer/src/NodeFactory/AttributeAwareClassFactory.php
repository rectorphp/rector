<?php

declare(strict_types=1);

namespace Rector\Utils\PHPStanAttributeTypeSyncer\NodeFactory;

use Nette\Utils\Strings;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Namespace_;
use Rector\Core\PhpParser\Builder\ClassBuilder;
use Rector\Core\PhpParser\Builder\NamespaceBuilder;
use Rector\Core\PhpParser\Builder\TraitUseBuilder;
use Rector\PhpdocParserPrinter\Attributes\AttributesTrait;
use Rector\PhpdocParserPrinter\Contract\AttributeAwareInterface;
use Rector\Utils\PHPStanAttributeTypeSyncer\ClassNaming\AttributeClassNaming;
use Rector\Utils\PHPStanAttributeTypeSyncer\ValueObject\Paths;

final class AttributeAwareClassFactory
{
    /**
     * @var AttributeClassNaming
     */
    private $attributeClassNaming;

    public function __construct(AttributeClassNaming $attributeClassNaming)
    {
        $this->attributeClassNaming = $attributeClassNaming;
    }

    public function createFromPhpDocParserNodeClass(string $nodeClass): Namespace_
    {
        if (Strings::contains($nodeClass, '\\Type\\')) {
            $namespace = Paths::NAMESPACE_TYPE_NODE;
        } else {
            $namespace = Paths::NAMESPACE_PHPDOC_NODE;
        }

        $namespaceBuilder = new NamespaceBuilder($namespace);

        $shortClassName = $this->attributeClassNaming->createAttributeAwareShortClassName($nodeClass);
        $classBuilder = $this->createClassBuilder($nodeClass, $shortClassName);

        $traitUseBuilder = new TraitUseBuilder(new FullyQualified(AttributesTrait::class));
        $classBuilder->addStmt($traitUseBuilder);

        $namespaceBuilder->addStmt($classBuilder->getNode());

        return $namespaceBuilder->getNode();
    }

    private function createClassBuilder(string $nodeClass, string $shortClassName): ClassBuilder
    {
        $classBuilder = new ClassBuilder($shortClassName);
        $classBuilder->makeFinal();
        $classBuilder->extend(new FullyQualified($nodeClass));
        $classBuilder->implement(new FullyQualified(AttributeAwareInterface::class));

        return $classBuilder;
    }
}
