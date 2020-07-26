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
        AttributeAwareClassFactoryFactory $attributeAwareClassFactoryFactory,
        AttributeClassNaming $attributeClassNaming,
        SymfonyStyle $symfonyStyle
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
            $message = sprintf('File "%s" already exists, skipping', $realTargetFilePath);
            $this->symfonyStyle->note($message);
            return;
        }

        $namespace = $this->attributeAwareClassFactoryFactory->createFromPhpDocParserNodeClass($phpDocParserNodeClass);
        $this->printNamespaceToFile($namespace, $targetFilePath);

        $this->reportGeneratedAttributeAwareNode($phpDocParserNodeClass, $targetFilePath);
    }

    private function resolveTargetFilePath(string $phpDocParserNodeClass): string
    {
        $shortClassName = $this->attributeClassNaming->createAttributeAwareFactoryShortClassName(
            $phpDocParserNodeClass
        );

        if (Strings::contains($phpDocParserNodeClass, '\\Type\\')) {
            return __DIR__ . '/../../../../packages/attribute-aware-php-doc/src/AttributeAwareNodeFactory/Type/' . $shortClassName . '.php';
        }

        return __DIR__ . '/../../../../packages/attribute-aware-php-doc/src/AttributeAwareNodeFactory/PhpDoc/' . $shortClassName . '.php';
    }

    private function reportGeneratedAttributeAwareNode(string $missingNodeClass, string $filePath): void
    {
        $attributeAwareFullyQualifiedClassName = $this->attributeClassNaming->createAttributeAwareClassName(
            $missingNodeClass
        );
        $message = sprintf(
            'Class "%s" now has freshly generated "%s" in "%s"',
            $missingNodeClass,
            $attributeAwareFullyQualifiedClassName,
            $filePath
        );

        $this->symfonyStyle->note($message);
    }
}
