<?php

declare(strict_types=1);

namespace Rector\PostRector\Rector;

use PhpParser\Node;
use PhpParser\Node\Name;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\CodingStyle\Node\NameImporter;
use Rector\Core\Configuration\Option;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\PhpDoc\NodeAnalyzer\DocBlockNameImporter;
use Symplify\PackageBuilder\Parameter\ParameterProvider;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use Rector\CodingStyle\ClassNameImport\ClassNameImportSkipper;

final class NameImportingPostRector extends AbstractPostRector
{
    /**
     * @var ParameterProvider
     */
    private $parameterProvider;

    /**
     * @var NameImporter
     */
    private $nameImporter;

    /**
     * @var DocBlockNameImporter
     */
    private $docBlockNameImporter;

    /**
     * @var ClassNameImportSkipper
     */
    private $classNameImportSkipper;

    public function __construct(
        ParameterProvider $parameterProvider,
        NameImporter $nameImporter,
        DocBlockNameImporter $docBlockNameImporter,
        ClassNameImportSkipper $classNameImportSkipper
    ) {
        $this->parameterProvider = $parameterProvider;
        $this->nameImporter = $nameImporter;
        $this->docBlockNameImporter = $docBlockNameImporter;
        $this->classNameImportSkipper = $classNameImportSkipper;
    }

    public function enterNode(Node $node): ?Node
    {
        $autoImportNames = $this->parameterProvider->provideParameter(Option::AUTO_IMPORT_NAMES);
        if (! $autoImportNames) {
            return null;
        }

        if ($node instanceof Name) {
            $name = $this->getName($node);

            if (! is_callable($name)) {
                return $this->nameImporter->importName($node);
            }

            if (substr_count($node->toString(), '\\') === 1) {
                return null;
            }

            if ($node->toCodeString() !== $node->toString()) {
                return $this->nameImporter->importName($node);
            }

            return $this->nameImporter->importName($node);
        }

        $importDocBlocks = (bool) $this->parameterProvider->provideParameter(Option::IMPORT_DOC_BLOCKS);
        if (! $importDocBlocks) {
            return null;
        }

        /** @var PhpDocInfo|null $phpDocInfo */
        $phpDocInfo = $node->getAttribute(AttributeKey::PHP_DOC_INFO);
        if ($phpDocInfo === null) {
            return null;
        }

        $hasChanged = $this->docBlockNameImporter->importNames($phpDocInfo, $node);
        if (! $hasChanged) {
            return null;
        }

        return $node;
    }

    public function getPriority(): int
    {
        return 600;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Imports fully qualified class names in parameter types, return types, extended classes, implemented, interfaces and even docblocks',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
$someClass = new \Some\FullyQualified\SomeClass();
CODE_SAMPLE
,
                    <<<'CODE_SAMPLE'
use Some\FullyQualified\SomeClass;

$someClass = new SomeClass();
CODE_SAMPLE
                ),
            ]
        );
    }
}
