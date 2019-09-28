<?php declare(strict_types=1);

namespace Rector\CodingStyle\Rector\Namespace_;

use PhpParser\Node;
use PhpParser\Node\Name;
use Rector\CodingStyle\Node\NameImporter;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * @see \Rector\CodingStyle\Tests\Rector\Namespace_\ImportFullyQualifiedNamesRector\ImportFullyQualifiedNamesRectorTest
 */
final class ImportFullyQualifiedNamesRector extends AbstractRector
{
    /**
     * @var bool
     */
    private $shouldImportDocBlocks = true;

    /**
     * @var NameImporter
     */
    private $nameImporter;

    public function __construct(NameImporter $nameImporter, bool $shouldImportDocBlocks = true)
    {
        $this->nameImporter = $nameImporter;
        $this->shouldImportDocBlocks = $shouldImportDocBlocks;
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
        $this->useAddingCommander->analyseFileInfoUseStatements($node);

        if ($node instanceof Name) {
            return $this->nameImporter->importName($node);
        }

        // process doc blocks
        if ($this->shouldImportDocBlocks) {
            $this->docBlockManipulator->importNames($node);
            return $node;
        }

        return null;
    }
}
