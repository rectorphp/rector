<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\Rector\v9\v3;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\BinaryOp\BooleanAnd;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Ternary;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Scalar\String_;
use PHPStan\Type\ObjectType;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/9.3/Deprecation-84994-BackendUtilitygetPidForModTSconfigDeprecated.html
 * @see \Ssch\TYPO3Rector\Tests\Rector\v9\v3\CopyMethodGetPidForModTSconfigRector\CopyMethodGetPidForModTSconfigRectorTest
 */
final class CopyMethodGetPidForModTSconfigRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Expr\StaticCall::class];
    }
    /**
     * @param StaticCall $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if (!$this->nodeTypeResolver->isMethodStaticCallOrClassMethodObjectType($node, new \PHPStan\Type\ObjectType('TYPO3\\CMS\\Backend\\Utility\\BackendUtility'))) {
            return null;
        }
        if (!$this->isName($node->name, 'getPidForModTSconfig')) {
            return null;
        }
        $tableVariable = $node->args[0]->value;
        if ($tableVariable instanceof \PhpParser\Node\Scalar\String_) {
            $tableVariable = new \PhpParser\Node\Expr\Variable('table');
            $this->nodesToAddCollector->addNodeBeforeNode(new \PhpParser\Node\Expr\Assign($tableVariable, $node->args[0]->value), $node);
        }
        return new \PhpParser\Node\Expr\Ternary(new \PhpParser\Node\Expr\BinaryOp\BooleanAnd(new \PhpParser\Node\Expr\BinaryOp\Identical($tableVariable, new \PhpParser\Node\Scalar\String_('pages')), $this->nodeFactory->createStaticCall('TYPO3\\CMS\\Core\\Utility\\MathUtility', 'canBeInterpretedAsInteger', [$node->args[1]])), $node->args[1]->value, $node->args[2]->value);
    }
    /**
     * @codeCoverageIgnore
     */
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Copy method getPidForModTSconfig of class BackendUtility over', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
use TYPO3\CMS\Backend\Utility\BackendUtility;BackendUtility::getPidForModTSconfig('pages', 1, 2);
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use TYPO3\CMS\Core\Utility\MathUtility;

$table = 'pages';
$uid = 1;
$pid = 2;
$table === 'pages' && MathUtility::canBeInterpretedAsInteger($uid) ? $uid : $pid;
CODE_SAMPLE
)]);
    }
}
