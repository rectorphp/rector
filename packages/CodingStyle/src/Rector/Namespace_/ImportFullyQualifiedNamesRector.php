<?php

declare(strict_types=1);

namespace Rector\CodingStyle\Rector\Namespace_;

use PhpParser\Node;
use PhpParser\Node\Name;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\CodingStyle\Application\NameImportingCommander;
use Rector\CodingStyle\Node\NameImporter;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\PhpDoc\NodeAnalyzer\DocBlockNameImporter;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;
use Rector\Testing\PHPUnit\PHPUnitEnvironment;

/**
 * @see \Rector\CodingStyle\Tests\Rector\Namespace_\ImportFullyQualifiedNamesRector\ImportFullyQualifiedNamesRectorTest
 * @see \Rector\CodingStyle\Tests\Rector\Namespace_\ImportFullyQualifiedNamesRector\NonNamespacedTest
 * @see \Rector\CodingStyle\Tests\Rector\Namespace_\ImportFullyQualifiedNamesRector\ImportRootNamespaceClassesDisabledTest
 */
final class ImportFullyQualifiedNamesRector extends AbstractRector
{
    /**
     * @var bool
     */
    private $importDocBlocks = true;

    /**
     * @var bool
     */
    private $autoImportNames = false;

    /**
     * @var NameImporter
     */
    private $nameImporter;

    /**
     * @var DocBlockNameImporter
     */
    private $docBlockNameImporter;

    public function __construct(
        NameImporter $nameImporter,
        bool $importDocBlocks,
        bool $autoImportNames,
        DocBlockNameImporter $docBlockNameImporter
    ) {
        $this->nameImporter = $nameImporter;
        $this->importDocBlocks = $importDocBlocks;
        $this->autoImportNames = $autoImportNames;
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
        /** prevents duplicated run with @see NameImportingCommander  */
        if ($this->autoImportNames && ! PHPUnitEnvironment::isPHPUnitRun()) {
            return null;
        }

        $this->useAddingCommander->analyseFileInfoUseStatements($node);

        if ($node instanceof Name) {
            return $this->nameImporter->importName($node);
        }

        // process doc blocks
        if ($this->importDocBlocks) {
            /** @var PhpDocInfo $phpDocInfo */
            $phpDocInfo = $node->getAttribute(AttributeKey::PHP_DOC_INFO);
            $this->docBlockNameImporter->importNames($phpDocInfo, $node);

//            $this->docBlockManipulator->importNames($node);
            return $node;
        }

        return null;
    }
}
