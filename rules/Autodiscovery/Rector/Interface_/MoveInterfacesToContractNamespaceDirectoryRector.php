<?php

declare (strict_types=1);
namespace Rector\Autodiscovery\Rector\Interface_;

use PhpParser\Node;
use PhpParser\Node\Stmt\Interface_;
use Rector\Core\Rector\AbstractRector;
use Rector\FileSystemRector\ValueObject\AddedFileWithNodes;
use Rector\FileSystemRector\ValueObjectFactory\AddedFileWithNodesFactory;
use Rector\Nette\NodeAnalyzer\Component\NetteControlFactoryInterfaceAnalyzer;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * Inspiration @see https://github.com/rectorphp/rector/pull/1865/files#diff-0d18e660cdb626958662641b491623f8
 *
 * @see \Rector\Tests\Autodiscovery\Rector\Interface_\MoveInterfacesToContractNamespaceDirectoryRector\MoveInterfacesToContractNamespaceDirectoryRectorTest
 */
final class MoveInterfacesToContractNamespaceDirectoryRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @var \Rector\Nette\NodeAnalyzer\Component\NetteControlFactoryInterfaceAnalyzer
     */
    private $netteControlFactoryInterfaceAnalyzer;
    /**
     * @var \Rector\FileSystemRector\ValueObjectFactory\AddedFileWithNodesFactory
     */
    private $addedFileWithNodesFactory;
    public function __construct(\Rector\Nette\NodeAnalyzer\Component\NetteControlFactoryInterfaceAnalyzer $netteControlFactoryInterfaceAnalyzer, \Rector\FileSystemRector\ValueObjectFactory\AddedFileWithNodesFactory $addedFileWithNodesFactory)
    {
        $this->netteControlFactoryInterfaceAnalyzer = $netteControlFactoryInterfaceAnalyzer;
        $this->addedFileWithNodesFactory = $addedFileWithNodesFactory;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Move interface to "Contract" namespace', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
// file: app/Exception/Rule.php

namespace App\Exception;

interface Rule
{
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
// file: app/Contract/Rule.php

namespace App\Contract;

interface Rule
{
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Stmt\Interface_::class];
    }
    /**
     * @param Interface_ $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if ($this->netteControlFactoryInterfaceAnalyzer->isComponentFactoryInterface($node)) {
            return null;
        }
        $addedFileWithNodes = $this->addedFileWithNodesFactory->createWithDesiredGroup($this->file->getSmartFileInfo(), $this->file, 'Contract');
        if (!$addedFileWithNodes instanceof \Rector\FileSystemRector\ValueObject\AddedFileWithNodes) {
            return null;
        }
        $this->removedAndAddedFilesCollector->removeFile($this->file->getSmartFileInfo());
        $this->removedAndAddedFilesCollector->addAddedFile($addedFileWithNodes);
        return null;
    }
}
