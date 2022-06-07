<?php

declare (strict_types=1);
namespace Rector\Nette\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Nette\Tests\Rector\FuncCall\FilePutContentsToFileSystemWriteRector\FilePutContentsToFileSystemWriteRectorTest
 */
final class FilePutContentsToFileSystemWriteRector extends AbstractRector
{
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Change file_put_contents() to FileSystem::write()', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        file_put_contents('file.txt', 'content');

        file_put_contents('file.txt', 'content_to_append', FILE_APPEND);
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        \Nette\Utils\FileSystem::write('file.txt', 'content');

        file_put_contents('file.txt', 'content_to_append', FILE_APPEND);
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [FuncCall::class];
    }
    /**
     * @param FuncCall $node
     */
    public function refactor(Node $node) : ?Node
    {
        if (!$this->isName($node, 'file_put_contents')) {
            return null;
        }
        if (\count($node->args) !== 2) {
            return null;
        }
        return $this->nodeFactory->createStaticCall('Nette\\Utils\\FileSystem', 'write', $node->args);
    }
}
