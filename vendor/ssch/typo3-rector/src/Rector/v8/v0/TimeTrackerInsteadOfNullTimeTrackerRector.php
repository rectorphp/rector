<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Ssch\TYPO3Rector\Rector\v8\v0;

use RectorPrefix20220606\PhpParser\Node;
use RectorPrefix20220606\PhpParser\Node\Expr\MethodCall;
use RectorPrefix20220606\PhpParser\Node\Expr\New_;
use RectorPrefix20220606\PhpParser\Node\Expr\StaticCall;
use RectorPrefix20220606\PhpParser\Node\Name;
use RectorPrefix20220606\PHPStan\Type\ObjectType;
use RectorPrefix20220606\Rector\Core\Rector\AbstractRector;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/8.0/Deprecation-73185-DeprecateNullTimeTracker.html
 * @see \Ssch\TYPO3Rector\Tests\Rector\v8\v0\TimeTrackerInsteadOfNullTimeTrackerRector\TimeTrackerInsteadOfNullTimeTrackerRectorTest
 */
final class TimeTrackerInsteadOfNullTimeTrackerRector extends AbstractRector
{
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [StaticCall::class, MethodCall::class, New_::class];
    }
    /**
     * @param MethodCall|StaticCall|New_ $node
     */
    public function refactor(Node $node) : ?Node
    {
        if ($node instanceof New_) {
            return $this->renameClassIfNeeded($node);
        }
        return $this->addAdditionalArgumentIfNeeded($node);
    }
    /**
     * @codeCoverageIgnore
     */
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Use class TimeTracker instead of NullTimeTracker', [new CodeSample(<<<'CODE_SAMPLE'
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
    private function addAdditionalArgumentIfNeeded(Node $node) : ?Node
    {
        if (!$node instanceof MethodCall && !$node instanceof StaticCall) {
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
    private function isMakeInstanceCall(Node $node) : bool
    {
        if (!$node instanceof StaticCall) {
            return \false;
        }
        if (!$this->nodeTypeResolver->isMethodStaticCallOrClassMethodObjectType($node, new ObjectType('TYPO3\\CMS\\Core\\Utility\\GeneralUtility'))) {
            return \false;
        }
        return $this->isName($node->name, 'makeInstance');
    }
    private function isObjectManagerCall(Node $node) : bool
    {
        if (!$node instanceof MethodCall) {
            return \false;
        }
        if (!$this->nodeTypeResolver->isMethodStaticCallOrClassMethodObjectType($node, new ObjectType('TYPO3\\CMS\\Extbase\\Object\\ObjectManager'))) {
            return \false;
        }
        return $this->isName($node->name, 'get');
    }
    private function renameClassIfNeeded(New_ $new) : ?Node
    {
        if (!$this->isObjectType($new, new ObjectType('TYPO3\\CMS\\Core\\TimeTracker\\NullTimeTracker'))) {
            return null;
        }
        $arguments = $this->nodeFactory->createArgs([$this->nodeFactory->createFalse()]);
        return new New_(new Name('TYPO3\\CMS\\Core\\TimeTracker\\TimeTracker'), $arguments);
    }
}
