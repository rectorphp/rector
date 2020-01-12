<?php

declare(strict_types=1);

namespace Rector\Utils\PHPStanAttributeTypeSyncer\Generator;

use Nette\Utils\Strings;
use Rector\Utils\PHPStanAttributeTypeSyncer\ClassNaming\AttributeClassNaming;
use Rector\Utils\PHPStanAttributeTypeSyncer\NodeFactory\AttributeAwareClassFactoryFactory;
use Symfony\Component\Console\Style\SymfonyStyle;

final class AttributeAwareNodeFactoryGenerator extends AbstractAttributeAwareNodeGenerator
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
     * @var AttributeAwareClassFactoryFactory
     */
    private $attributeAwareClassFactoryFactory;

    public function __construct(
        AttributeClassNaming $attributeClassNaming,
        SymfonyStyle $symfonyStyle,
        AttributeAwareClassFactoryFactory $attributeAwareClassFactoryFactory
    ) {
        $this->attributeClassNaming = $attributeClassNaming;
        $this->symfonyStyle = $symfonyStyle;
        $this->attributeAwareClassFactoryFactory = $attributeAwareClassFactoryFactory;
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

        $namespace = $this->attributeAwareClassFactoryFactory->createFromPhpDocParserNodeClass($phpDocParserNodeClass);
        $this->printNamespaceToFile($namespace, $targetFilePath);

        $this->reportGeneratedAttributeAwareNode($phpDocParserNodeClass, $targetFilePath);
    }

    private function reportGeneratedAttributeAwareNode(string $missingNodeClass, string $filePath): void
    {
        $attributeAwareFullyQualifiedClassName = $this->attributeClassNaming->createAttributeAwareClassName(
            $missingNodeClass
        );

        $this->symfonyStyle->note(sprintf(
            'Class "%s" now has freshly generated "%s" in "%s"',
            $missingNodeClass,
            $attributeAwareFullyQualifiedClassName,
            $filePath
        ));
    }

    private function resolveTargetFilePath(string $phpDocParserNodeClass): string
    {
        $shortClassName = $this->attributeClassNaming->createAttributeAwareFactoryShortClassName(
            $phpDocParserNodeClass
        );

        if (Strings::contains($phpDocParserNodeClass, '\\Type\\')) {
            return __DIR__ . '/../../../../packages/AttributeAwarePhpDoc/src/AttributeAwareNodeFactory/Type/' . $shortClassName . '.php';
        }

        return __DIR__ . '/../../../../packages/AttributeAwarePhpDoc/src/AttributeAwareNodeFactory/PhpDoc/' . $shortClassName . '.php';
    }
}
