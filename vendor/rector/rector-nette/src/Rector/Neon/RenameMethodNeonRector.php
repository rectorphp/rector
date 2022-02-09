<?php

declare (strict_types=1);
namespace Rector\Nette\Rector\Neon;

use RectorPrefix20220209\Nette\Neon\Node;
use Rector\Nette\Contract\Rector\NeonRectorInterface;
use Rector\Nette\NeonParser\Node\Service_\SetupMethodCall;
use Rector\Renaming\Collector\MethodCallRenameCollector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Nette\Tests\Rector\Neon\RenameMethodNeonRector\RenameMethodNeonRectorTest
 *
 * @implements NeonRectorInterface<SetupMethodCall>
 */
final class RenameMethodNeonRector implements \Rector\Nette\Contract\Rector\NeonRectorInterface
{
    /**
     * @readonly
     * @var \Rector\Renaming\Collector\MethodCallRenameCollector
     */
    private $methodCallRenameCollector;
    public function __construct(\Rector\Renaming\Collector\MethodCallRenameCollector $methodCallRenameCollector)
    {
        $this->methodCallRenameCollector = $methodCallRenameCollector;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Renames method calls in NEON configs', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
services:
    -
        class: SomeClass
        setup:
            - oldCall
CODE_SAMPLE
, <<<'CODE_SAMPLE'
services:
    -
        class: SomeClass
        setup:
            - newCall
CODE_SAMPLE
)]);
    }
    public function getNodeType() : string
    {
        return \Rector\Nette\NeonParser\Node\Service_\SetupMethodCall::class;
    }
    /**
     * @param SetupMethodCall $node
     * @return \Nette\Neon\Node|null
     */
    public function enterNode(\RectorPrefix20220209\Nette\Neon\Node $node)
    {
        foreach ($this->methodCallRenameCollector->getMethodCallRenames() as $methodCallRename) {
            if (!\is_a($node->className, $methodCallRename->getClass(), \true)) {
                continue;
            }
            if ($node->getMethodName() !== $methodCallRename->getOldMethod()) {
                continue;
            }
            $node->methodNameLiteralNode->value = $methodCallRename->getNewMethod();
            return $node;
        }
        return null;
    }
}
