<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\Nette\Rector\Neon;

use RectorPrefix20220606\Nette\Neon\Node;
use RectorPrefix20220606\Rector\Nette\Contract\Rector\NeonRectorInterface;
use RectorPrefix20220606\Rector\Nette\NeonParser\Node\Service_\SetupMethodCall;
use RectorPrefix20220606\Rector\Renaming\Collector\MethodCallRenameCollector;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Nette\Tests\Rector\Neon\RenameMethodNeonRector\RenameMethodNeonRectorTest
 *
 * @implements NeonRectorInterface<SetupMethodCall>
 */
final class RenameMethodNeonRector implements NeonRectorInterface
{
    /**
     * @readonly
     * @var \Rector\Renaming\Collector\MethodCallRenameCollector
     */
    private $methodCallRenameCollector;
    public function __construct(MethodCallRenameCollector $methodCallRenameCollector)
    {
        $this->methodCallRenameCollector = $methodCallRenameCollector;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Renames method calls in NEON configs', [new CodeSample(<<<'CODE_SAMPLE'
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
        return SetupMethodCall::class;
    }
    /**
     * @param SetupMethodCall $node
     * @return \Nette\Neon\Node|null
     */
    public function enterNode(Node $node)
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
