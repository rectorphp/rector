<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Ssch\TYPO3Rector\Rector\v7\v0;

use RectorPrefix20220606\PhpParser\Node;
use RectorPrefix20220606\PhpParser\Node\Expr\StaticCall;
use RectorPrefix20220606\PHPStan\Type\ObjectType;
use RectorPrefix20220606\Rector\Core\Rector\AbstractRector;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/7.0/Breaking-61863-ConnectDbFunctionRemoved.html
 * @see \Ssch\TYPO3Rector\Tests\Rector\v7\v0\RemoveMethodCallConnectDbRector\RemoveMethodCallConnectDbRectorTest
 */
final class RemoveMethodCallConnectDbRector extends AbstractRector
{
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [StaticCall::class];
    }
    /**
     * @param StaticCall $node
     */
    public function refactor(Node $node) : ?Node
    {
        if (!$this->nodeTypeResolver->isMethodStaticCallOrClassMethodObjectType($node, new ObjectType('TYPO3\\CMS\\Frontend\\Utility\\EidUtility'))) {
            return null;
        }
        if (!$this->isName($node->name, 'connectDB')) {
            return null;
        }
        $this->removeNode($node);
        return $node;
    }
    /**
     * @codeCoverageIgnore
     */
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Remove EidUtility::connectDB() call', [new CodeSample(<<<'CODE_SAMPLE'
EidUtility::connectDB()
CODE_SAMPLE
, <<<'CODE_SAMPLE'
-
CODE_SAMPLE
)]);
    }
}
