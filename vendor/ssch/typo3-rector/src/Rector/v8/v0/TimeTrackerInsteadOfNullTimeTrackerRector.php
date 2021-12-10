<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\Rector\v8\v0;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Name;
use PHPStan\Type\ObjectType;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/8.0/Deprecation-73185-DeprecateNullTimeTracker.html
 * @see \Ssch\TYPO3Rector\Tests\Rector\v8\v0\TimeTrackerInsteadOfNullTimeTrackerRector\TimeTrackerInsteadOfNullTimeTrackerRectorTest
 */
final class TimeTrackerInsteadOfNullTimeTrackerRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Expr\StaticCall::class, \PhpParser\Node\Expr\MethodCall::class, \PhpParser\Node\Expr\New_::class];
    }
    /**
     * @param MethodCall|StaticCall|New_ $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if ($node instanceof \PhpParser\Node\Expr\New_) {
            return $this->renameClassIfNeeded($node);
        }
        return $this->addAdditionalArgumentIfNeeded($node);
    }
    /**
     * @codeCoverageIgnore
     */
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Use class TimeTracker instead of NullTimeTracker', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
use TYPO3\CMS\Core\TimeTracker\NullTimeTracker;
use TYPO3\CMS\Core\Utility\GeneralUtility;
$timeTracker1 = new NullTimeTracker();
$timeTracker2 = GeneralUtility::makeInstance(NullTimeTracker::class);
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use TYPO3\CMS\Core\TimeTracker\TimeTracker;
use TYPO3\CMS\Core\Utility\GeneralUtility;
$timeTracker1 = new TimeTracker(false);
$timeTracker2 = GeneralUtility::makeInstance(TimeTracker::class, false);
CODE_SAMPLE
)]);
    }
    private function addAdditionalArgumentIfNeeded(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if (!$node instanceof \PhpParser\Node\Expr\MethodCall && !$node instanceof \PhpParser\Node\Expr\StaticCall) {
            return null;
        }
        if (!$this->isMakeInstanceCall($node) && !$this->isObjectManagerCall($node)) {
            return null;
        }
        if (!isset($node->args[0])) {
            return null;
        }
        $value = $this->valueResolver->getValue($node->args[0]->value);
        if ('TYPO3\\CMS\\Core\\TimeTracker\\NullTimeTracker' !== $value) {
            return null;
        }
        $timeTracker = $this->nodeFactory->createClassConstReference('TYPO3\\CMS\\Core\\TimeTracker\\TimeTracker');
        $node->args[0] = $this->nodeFactory->createArg($timeTracker);
        $node->args[1] = $this->nodeFactory->createArg(\false);
        return $node;
    }
    private function isMakeInstanceCall(\PhpParser\Node $node) : bool
    {
        if (!$node instanceof \PhpParser\Node\Expr\StaticCall) {
            return \false;
        }
        if (!$this->nodeTypeResolver->isMethodStaticCallOrClassMethodObjectType($node, new \PHPStan\Type\ObjectType('TYPO3\\CMS\\Core\\Utility\\GeneralUtility'))) {
            return \false;
        }
        return $this->isName($node->name, 'makeInstance');
    }
    private function isObjectManagerCall(\PhpParser\Node $node) : bool
    {
        if (!$node instanceof \PhpParser\Node\Expr\MethodCall) {
            return \false;
        }
        if (!$this->nodeTypeResolver->isMethodStaticCallOrClassMethodObjectType($node, new \PHPStan\Type\ObjectType('TYPO3\\CMS\\Extbase\\Object\\ObjectManager'))) {
            return \false;
        }
        return $this->isName($node->name, 'get');
    }
    private function renameClassIfNeeded(\PhpParser\Node\Expr\New_ $node) : ?\PhpParser\Node
    {
        if (!$this->isObjectType($node, new \PHPStan\Type\ObjectType('TYPO3\\CMS\\Core\\TimeTracker\\NullTimeTracker'))) {
            return null;
        }
        $arguments = $this->nodeFactory->createArgs([$this->nodeFactory->createFalse()]);
        return new \PhpParser\Node\Expr\New_(new \PhpParser\Node\Name('TYPO3\\CMS\\Core\\TimeTracker\\TimeTracker'), $arguments);
    }
}
