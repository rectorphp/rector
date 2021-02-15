<?php

declare(strict_types=1);

namespace Rector\Autodiscovery\Rector\FileNode;

use PhpParser\Node;
use PhpParser\Node\Stmt\Interface_;
use Rector\Core\PhpParser\Node\CustomNode\FileNode;
use Rector\Core\Rector\AbstractRector;
use Rector\FileSystemRector\ValueObject\MovedFileWithNodes;
use Rector\FileSystemRector\ValueObjectFactory\MovedFileWithNodesFactory;
use Rector\NetteToSymfony\NodeAnalyzer\NetteControlFactoryInterfaceAnalyzer;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @sponsor Thanks https://spaceflow.io/ for sponsoring this rule - visit them on https://github.com/SpaceFlow-app
 *
 * Inspiration @see https://github.com/rectorphp/rector/pull/1865/files#diff-0d18e660cdb626958662641b491623f8
 *
 * @see \Rector\Autodiscovery\Tests\Rector\FileNode\MoveInterfacesToContractNamespaceDirectoryRector\MoveInterfacesToContractNamespaceDirectoryRectorTest
 */
final class MoveInterfacesToContractNamespaceDirectoryRector extends AbstractRector
{
    /**
     * @var NetteControlFactoryInterfaceAnalyzer
     */
    private $netteControlFactoryInterfaceAnalyzer;

    /**
     * @var MovedFileWithNodesFactory
     */
    private $movedFileWithNodesFactory;

    public function __construct(
        NetteControlFactoryInterfaceAnalyzer $netteControlFactoryInterfaceAnalyzer,
        MovedFileWithNodesFactory $movedFileWithNodesFactory
    ) {
        $this->netteControlFactoryInterfaceAnalyzer = $netteControlFactoryInterfaceAnalyzer;
        $this->movedFileWithNodesFactory = $movedFileWithNodesFactory;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Move interface to "Contract" namespace', [
            new CodeSample(
<<<'CODE_SAMPLE'
// file: app/Exception/Rule.php

namespace App\Exception;

interface Rule
{
}
CODE_SAMPLE
            ,
            <<<'CODE_SAMPLE'
// file: app/Contract/Rule.php

namespace App\Contract;

interface Rule
{
}
CODE_SAMPLE
        ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [FileNode::class];
    }

    /**
     * @param FileNode $node
     */
    public function refactor(Node $node): ?Node
    {
        $interface = $this->betterNodeFinder->findFirstInstanceOf([$node], Interface_::class);
        if (! $interface instanceof Interface_) {
            return null;
        }

        if ($this->netteControlFactoryInterfaceAnalyzer->isComponentFactoryInterface($interface)) {
            return null;
        }

        $movedFileWithNodes = $this->movedFileWithNodesFactory->createWithDesiredGroup(
            $node->getFileInfo(),
            $node->stmts,
            'Contract'
        );
        if (! $movedFileWithNodes instanceof MovedFileWithNodes) {
            return null;
        }

        $this->removedAndAddedFilesCollector->addMovedFile($movedFileWithNodes);

        return null;
    }
}
