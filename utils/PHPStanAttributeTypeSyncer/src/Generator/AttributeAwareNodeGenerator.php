<?php

declare(strict_types=1);

namespace Rector\Utils\PHPStanAttributeTypeSyncer\Generator;

use Nette\Utils\Strings;
use Rector\Utils\PHPStanAttributeTypeSyncer\ClassNaming\AttributeClassNaming;
use Rector\Utils\PHPStanAttributeTypeSyncer\NodeFactory\AttributeAwareClassFactory;
use Symfony\Component\Console\Style\SymfonyStyle;

final class AttributeAwareNodeGenerator extends AbstractAttributeAwareNodeGenerator
{
    /**
     * @var AttributeClassNaming
     */
    private $attributeClassNaming;

    /**
     * @var SymfonyStyle
     */
    private $symfonyStyle;

    /**
     * @var AttributeAwareClassFactory
     */
    private $attributeAwareClassFactory;

    public function __construct(
        AttributeClassNaming $attributeClassNaming,
        SymfonyStyle $symfonyStyle,
        AttributeAwareClassFactory $attributeAwareClassFactory
    ) {
        $this->attributeClassNaming = $attributeClassNaming;
        $this->symfonyStyle = $symfonyStyle;
        $this->attributeAwareClassFactory = $attributeAwareClassFactory;
    }

    public function generateFromPhpDocParserNodeClass(string $phpDocParserNodeClass): void
    {
        $targetFilePath = $this->resolveTargetFilePath($phpDocParserNodeClass);

        // prevent file override
        if (file_exists($targetFilePath)) {
            $realTargetFilePath = realpath($targetFilePath);
            $this->symfonyStyle->note(sprintf('File "%s" already exists, skipping', $realTargetFilePath));
            return;
        }

        $namespace = $this->attributeAwareClassFactory->createFromPhpDocParserNodeClass($phpDocParserNodeClass);

        $this->printNamespaceToFile($namespace, $targetFilePath);

        $this->reportGeneratedAttributeAwareNode($phpDocParserNodeClass);
    }

    private function resolveTargetFilePath(string $phpDocParserNodeClass): string
    {
        $shortClassName = $this->attributeClassNaming->createAttributeAwareShortClassName($phpDocParserNodeClass);

        if (Strings::contains($phpDocParserNodeClass, '\\Type\\')) {
            return __DIR__ . '/../../../../packages/attribute-aware-php-doc/src/Ast/Type/' . $shortClassName . '.php';
        }

        return __DIR__ . '/../../../../packages/attribute-aware-php-doc/src/Ast/PhpDoc/' . $shortClassName . '.php';
    }

    private function reportGeneratedAttributeAwareNode(string $missingNodeClass): void
    {
        $attributeAwareFullyQualifiedClassName = $this->attributeClassNaming->createAttributeAwareClassName(
            $missingNodeClass
        );

        $this->symfonyStyle->note(sprintf(
            'Class "%s" now has freshly generated "%s"',
            $missingNodeClass,
            $attributeAwareFullyQualifiedClassName
        ));
    }
}
