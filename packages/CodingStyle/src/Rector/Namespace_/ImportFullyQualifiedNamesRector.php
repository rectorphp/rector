<?php

declare(strict_types=1);

namespace Rector\CodingStyle\Rector\Namespace_;

use PhpParser\Node;
use PhpParser\Node\Name;
use Rector\CodingStyle\Application\NameImportingCommander;
use Rector\CodingStyle\Node\NameImporter;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\ConfiguredCodeSample;
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
    private $shouldImportDocBlocks = true;

    /**
     * @var bool
     */
    private $shouldImportRootNamespaceClasses = true;

    /**
     * @var NameImporter
     */
    private $nameImporter;

    /**
     * @var bool
     */
    private $autoImportNames = false;

    public function __construct(
        NameImporter $nameImporter,
        bool $shouldImportDocBlocks = true,
        bool $shouldImportRootNamespaceClasses = true,
        bool $autoImportNames
    ) {
        $this->nameImporter = $nameImporter;
        $this->shouldImportDocBlocks = $shouldImportDocBlocks;
        $this->shouldImportRootNamespaceClasses = $shouldImportRootNamespaceClasses;
        $this->autoImportNames = $autoImportNames;
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
            new ConfiguredCodeSample(
                <<<'PHP'
class SomeClass
{
    public function create()
    {
        return SomeAnother\AnotherClass;
    }

    public function createDate()
    {
        return new \DateTime(); // this remains untouched
    }
}
PHP
                ,
                <<<'PHP'
use SomeAnother\AnotherClass;

class SomeClass
{
    public function create()
    {
        return AnotherClass;
    }

    public function createDate()
    {
        return new \DateTime(); // this remains untouched
    }
}
PHP
                ,
                [
                    '$shouldImportRootNamespaceClasses' => false,
                ]
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
            // Importing root namespace classes (like \DateTime) is optional
            if (! $this->shouldImportRootNamespaceClasses) {
                $name = $this->getName($node);
                if ($name !== null && substr_count($name, '\\') === 0) {
                    return null;
                }
            }

            return $this->nameImporter->importName($node);
        }

        // process doc blocks
        if ($this->shouldImportDocBlocks) {
            $this->docBlockManipulator->importNames($node, $this->shouldImportRootNamespaceClasses);
            return $node;
        }

        return null;
    }
}
