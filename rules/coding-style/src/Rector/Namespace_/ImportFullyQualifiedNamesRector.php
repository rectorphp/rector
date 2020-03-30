<?php

declare(strict_types=1);

namespace Rector\CodingStyle\Rector\Namespace_;

use PhpParser\Node;
use PhpParser\Node\Name;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\CodingStyle\Application\NameImportingCommander;
use Rector\CodingStyle\Node\NameImporter;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\Core\Testing\PHPUnit\PHPUnitEnvironment;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\PhpDoc\NodeAnalyzer\DocBlockNameImporter;

/**
 * This rule must be last, as it shortens nodes before using them by other Rectors, thus breaking them.
 *
 * This file remains just for testing.
 * Breaks other rectors by making name nodes short, FQN → short names
 *
 * @see \Rector\CodingStyle\Tests\Rector\Namespace_\ImportFullyQualifiedNamesRector\ImportFullyQualifiedNamesRectorTest
 * @see \Rector\CodingStyle\Tests\Rector\Namespace_\ImportFullyQualifiedNamesRector\NonNamespacedTest
 * @see \Rector\CodingStyle\Tests\Rector\Namespace_\ImportFullyQualifiedNamesRector\ImportRootNamespaceClassesDisabledTest
 */
final class ImportFullyQualifiedNamesRector extends AbstractRector
{
    /**
     * @var NameImporter
     */
    private $nameImporter;

    /**
     * @var DocBlockNameImporter
     */
    private $docBlockNameImporter;

    public function __construct(NameImporter $nameImporter, DocBlockNameImporter $docBlockNameImporter)
    {
        $this->nameImporter = $nameImporter;
        $this->docBlockNameImporter = $docBlockNameImporter;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Import fully qualified names to use statements', [
            new CodeSample(
                <<<'PHP'
class SomeClass
{
    public function create()
    {
          return SomeAnother\AnotherClass;
    }

    public function createDate()
    {
        return new \DateTime();
    }
}
PHP
                ,
                <<<'PHP'
use SomeAnother\AnotherClass;
use DateTime;

class SomeClass
{
    public function create()
    {
          return AnotherClass;
    }

    public function createDate()
    {
        return new DateTime();
    }
}
PHP
            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [Name::class, Node::class];
    }

    /**
     * @param Name|Node $node
     */
    public function refactor(Node $node): ?Node
    {
        // this file remains just for testing
        // breaks other rectors by making name nodes short, FQN → short names
        /** prevents duplicated run with @see NameImportingCommander  */
        if (! PHPUnitEnvironment::isPHPUnitRun()) {
            return null;
        }

        $this->useAddingCommander->analyseFileInfoUseStatements($node);

        if ($node instanceof Name) {
            return $this->nameImporter->importName($node);
        }

        // process doc blocks
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
}
