<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Ssch\TYPO3Rector\Rector\v8\v0;

use RectorPrefix20220606\PhpParser\Node;
use RectorPrefix20220606\PhpParser\Node\Expr\StaticCall;
use RectorPrefix20220606\PhpParser\Node\Stmt\ClassMethod;
use RectorPrefix20220606\PHPStan\Type\ObjectType;
use RectorPrefix20220606\Rector\Core\Rector\AbstractRector;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/8.0/Breaking-72370-RemovedDeprecatedCodeFromExtbase.html
 * @see \Ssch\TYPO3Rector\Tests\Rector\v8\v0\RemoveWakeupCallFromEntityRector\RemoveWakeupCallFromEntityRectorTest
 */
final class RemoveWakeupCallFromEntityRector extends AbstractRector
{
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [StaticCall::class];
    }
    /**
     * @param ClassMethod $node
     */
    public function refactor(Node $node) : ?Node
    {
        if (!$this->nodeTypeResolver->isMethodStaticCallOrClassMethodObjectType($node, new ObjectType('TYPO3\\CMS\\Extbase\\DomainObject\\AbstractDomainObject'))) {
            return null;
        }
        if (!$this->isName($node->name, '__wakeup')) {
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
        return new RuleDefinition('Remove __wakeup call for AbstractDomainObject', [new CodeSample(<<<'CODE_SAMPLE'
use TYPO3\CMS\Extbase\DomainObject\AbstractDomainObject;

class MyWakeupCallerClass extends AbstractDomainObject
{
    private $mySpecialResourceAfterWakeUp;

    public function __wakeup()
    {
        $this->mySpecialResourceAfterWakeUp = fopen(__FILE__, 'wb');
        parent::__wakeup();
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use TYPO3\CMS\Extbase\DomainObject\AbstractDomainObject;

class MyWakeupCallerClass extends AbstractDomainObject
{
    private $mySpecialResourceAfterWakeUp;

    public function __wakeup()
    {
        $this->mySpecialResourceAfterWakeUp = fopen(__FILE__, 'wb');
    }
}
CODE_SAMPLE
)]);
    }
}
