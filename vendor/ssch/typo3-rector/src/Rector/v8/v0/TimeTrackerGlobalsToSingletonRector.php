<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\Rector\v8\v0;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use Rector\Core\Rector\AbstractRector;
use Ssch\TYPO3Rector\Helper\Typo3NodeResolver;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/8.0/Breaking-73504-MakeTimeTrackerASingleton.html
 * @see \Ssch\TYPO3Rector\Tests\Rector\v8\v0\TimeTrackerGlobalsToSingletonRector\TimeTrackerGlobalsToSingletonRectorTest
 */
final class TimeTrackerGlobalsToSingletonRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @readonly
     * @var \Ssch\TYPO3Rector\Helper\Typo3NodeResolver
     */
    private $typo3NodeResolver;
    public function __construct(\Ssch\TYPO3Rector\Helper\Typo3NodeResolver $typo3NodeResolver)
    {
        $this->typo3NodeResolver = $typo3NodeResolver;
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Expr\MethodCall::class];
    }
    /**
     * @param MethodCall $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if (!$this->typo3NodeResolver->isAnyMethodCallOnGlobals($node, \Ssch\TYPO3Rector\Helper\Typo3NodeResolver::TIME_TRACKER)) {
            return null;
        }
        $classConstant = $this->nodeFactory->createClassConstReference('TYPO3\\CMS\\Core\\TimeTracker\\TimeTracker');
        $staticCall = $this->nodeFactory->createStaticCall('TYPO3\\CMS\\Core\\Utility\\GeneralUtility', 'makeInstance', [$classConstant]);
        $methodCallName = $this->getName($node->name);
        if (null === $methodCallName) {
            return null;
        }
        return $this->nodeFactory->createMethodCall($staticCall, $methodCallName, $node->args);
    }
    /**
     * @codeCoverageIgnore
     */
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Substitute $GLOBALS[\'TT\'] method calls', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
$GLOBALS['TT']->setTSlogMessage('content');
CODE_SAMPLE
, <<<'CODE_SAMPLE'
GeneralUtility::makeInstance(TimeTracker::class)->setTSlogMessage('content');
CODE_SAMPLE
)]);
    }
}
