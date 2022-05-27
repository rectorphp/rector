<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\Rector\v9\v4;

use PhpParser\Node;
use PhpParser\Node\Expr\Cast\String_;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Type\ObjectType;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/9.4/Deprecation-84387-DeprecatedMethodAndPropertyInSchedulerModuleController.html
 * @see \Ssch\TYPO3Rector\Tests\Rector\v9\v4\AdditionalFieldProviderRector\AdditionalFieldProviderRectorTest
 */
final class AdditionalFieldProviderRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Stmt\Class_::class, \PhpParser\Node\Expr\MethodCall::class, \PhpParser\Node\Expr\PropertyFetch::class];
    }
    /**
     * @param Class_|PropertyFetch|MethodCall $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if ($node instanceof \PhpParser\Node\Stmt\Class_) {
            return $this->refactorClass($node);
        }
        if ($node instanceof \PhpParser\Node\Expr\PropertyFetch) {
            return $this->refactorPropertyFetch($node);
        }
        return $this->refactorMethodCall($node);
    }
    /**
     * @codeCoverageIgnore
     */
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Refactor AdditionalFieldProvider classes', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
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
    private function shouldSkip(\PhpParser\Node\Stmt\Class_ $class) : bool
    {
        foreach ($class->implements as $implement) {
            if ($this->isName($implement, 'TYPO3\\CMS\\Scheduler\\AdditionalFieldProviderInterface')) {
                return \false;
            }
        }
        return \true;
    }
    private function refactorClass(\PhpParser\Node\Stmt\Class_ $class) : ?\PhpParser\Node
    {
        if ($this->shouldSkip($class)) {
            return null;
        }
        $class->extends = new \PhpParser\Node\Name\FullyQualified('TYPO3\\CMS\\Scheduler\\AbstractAdditionalFieldProvider');
        $implements = [];
        foreach ($class->implements as $implement) {
            if (!$this->isName($implement, 'TYPO3\\CMS\\Scheduler\\AdditionalFieldProviderInterface')) {
                $implements[] = $implement;
            }
        }
        $class->implements = $implements;
        return $class;
    }
    private function refactorPropertyFetch(\PhpParser\Node\Expr\PropertyFetch $node) : ?\PhpParser\Node
    {
        if (!$this->isObjectType($node->var, new \PHPStan\Type\ObjectType('TYPO3\\CMS\\Scheduler\\Controller\\SchedulerModuleController'))) {
            return null;
        }
        if (!$this->isName($node->name, 'CMD')) {
            return null;
        }
        $methodCall = $this->nodeFactory->createMethodCall($node->var, 'getCurrentAction');
        return new \PhpParser\Node\Expr\Cast\String_($methodCall);
    }
    private function refactorMethodCall(\PhpParser\Node\Expr\MethodCall $methodCall) : ?\PhpParser\Node
    {
        if (!$this->nodeTypeResolver->isMethodStaticCallOrClassMethodObjectType($methodCall, new \PHPStan\Type\ObjectType('TYPO3\\CMS\\Scheduler\\Controller\\SchedulerModuleController'))) {
            return null;
        }
        if (!$this->isName($methodCall->name, 'addMessage')) {
            return null;
        }
        return $this->nodeFactory->createMethodCall('this', 'addMessage', $methodCall->args);
    }
}
