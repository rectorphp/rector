<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Ssch\TYPO3Rector\Rector\v9\v4;

use RectorPrefix20220606\PhpParser\Node;
use RectorPrefix20220606\PhpParser\Node\Expr\Cast\String_;
use RectorPrefix20220606\PhpParser\Node\Expr\MethodCall;
use RectorPrefix20220606\PhpParser\Node\Expr\PropertyFetch;
use RectorPrefix20220606\PhpParser\Node\Name\FullyQualified;
use RectorPrefix20220606\PhpParser\Node\Stmt\Class_;
use RectorPrefix20220606\PHPStan\Type\ObjectType;
use RectorPrefix20220606\Rector\Core\Rector\AbstractRector;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/9.4/Deprecation-84387-DeprecatedMethodAndPropertyInSchedulerModuleController.html
 * @see \Ssch\TYPO3Rector\Tests\Rector\v9\v4\AdditionalFieldProviderRector\AdditionalFieldProviderRectorTest
 */
final class AdditionalFieldProviderRector extends AbstractRector
{
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [Class_::class, MethodCall::class, PropertyFetch::class];
    }
    /**
     * @param Class_|PropertyFetch|MethodCall $node
     */
    public function refactor(Node $node) : ?Node
    {
        if ($node instanceof Class_) {
            return $this->refactorClass($node);
        }
        if ($node instanceof PropertyFetch) {
            return $this->refactorPropertyFetch($node);
        }
        return $this->refactorMethodCall($node);
    }
    /**
     * @codeCoverageIgnore
     */
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Refactor AdditionalFieldProvider classes', [new CodeSample(<<<'CODE_SAMPLE'
use TYPO3\CMS\Scheduler\AdditionalFieldProviderInterface;
use TYPO3\CMS\Scheduler\Controller\SchedulerModuleController;

class FileCleanupTaskAdditionalFields implements AdditionalFieldProviderInterface
{
    public function getAdditionalFields (array &$taskInfo, $task, SchedulerModuleController $parentObject)
    {
        if (!isset($taskInfo[$this->fieldAgeInDays])) {
            if ($parentObject->CMD == 'edit') {
                $taskInfo[$this->fieldAgeInDays] = (int)$task->ageInDays;
            } else {
                $taskInfo[$this->fieldAgeInDays] = '';
            }
        }
   }
}

CODE_SAMPLE
, <<<'CODE_SAMPLE'
use TYPO3\CMS\Scheduler\AbstractAdditionalFieldProvider;
use TYPO3\CMS\Scheduler\Controller\SchedulerModuleController;

class FileCleanupTaskAdditionalFields extends AbstractAdditionalFieldProvider
{
    public function getAdditionalFields (array &$taskInfo, $task, SchedulerModuleController $parentObject)
    {
        if (!isset($taskInfo[$this->fieldAgeInDays])) {
            if ((string) $parentObject->getCurrentAction() == 'edit') {
                $taskInfo[$this->fieldAgeInDays] = (int)$task->ageInDays;
            } else {
                $taskInfo[$this->fieldAgeInDays] = '';
            }
        }
   }
}
CODE_SAMPLE
)]);
    }
    private function shouldSkip(Class_ $class) : bool
    {
        foreach ($class->implements as $implement) {
            if ($this->isName($implement, 'TYPO3\\CMS\\Scheduler\\AdditionalFieldProviderInterface')) {
                return \false;
            }
        }
        return \true;
    }
    private function refactorClass(Class_ $class) : ?Node
    {
        if ($this->shouldSkip($class)) {
            return null;
        }
        $class->extends = new FullyQualified('TYPO3\\CMS\\Scheduler\\AbstractAdditionalFieldProvider');
        $implements = [];
        foreach ($class->implements as $implement) {
            if (!$this->isName($implement, 'TYPO3\\CMS\\Scheduler\\AdditionalFieldProviderInterface')) {
                $implements[] = $implement;
            }
        }
        $class->implements = $implements;
        return $class;
    }
    private function refactorPropertyFetch(PropertyFetch $node) : ?Node
    {
        if (!$this->isObjectType($node->var, new ObjectType('TYPO3\\CMS\\Scheduler\\Controller\\SchedulerModuleController'))) {
            return null;
        }
        if (!$this->isName($node->name, 'CMD')) {
            return null;
        }
        $methodCall = $this->nodeFactory->createMethodCall($node->var, 'getCurrentAction');
        return new String_($methodCall);
    }
    private function refactorMethodCall(MethodCall $methodCall) : ?Node
    {
        if (!$this->nodeTypeResolver->isMethodStaticCallOrClassMethodObjectType($methodCall, new ObjectType('TYPO3\\CMS\\Scheduler\\Controller\\SchedulerModuleController'))) {
            return null;
        }
        if (!$this->isName($methodCall->name, 'addMessage')) {
            return null;
        }
        return $this->nodeFactory->createMethodCall('this', 'addMessage', $methodCall->args);
    }
}
