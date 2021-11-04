<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\Rector\v11\v3;

use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp\Equal;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Return_;
use PHPStan\Type\ObjectType;
use Rector\Core\Rector\AbstractRector;
use Rector\Php72\NodeFactory\AnonymousFunctionFactory;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/11.3/Deprecation-94311-DeprecatedGeneralUtilityrmFromList.html
 * @see \Ssch\TYPO3Rector\Tests\Rector\v11\v3\SubstituteMethodRmFromListOfGeneralUtilityRector\SubstituteMethodRmFromListOfGeneralUtilityRectorTest
 */
final class SubstituteMethodRmFromListOfGeneralUtilityRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @var \Rector\Php72\NodeFactory\AnonymousFunctionFactory
     */
    private $anonymousFunctionFactory;
    public function __construct(\Rector\Php72\NodeFactory\AnonymousFunctionFactory $anonymousFunctionFactory)
    {
        $this->anonymousFunctionFactory = $anonymousFunctionFactory;
    }
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
        if (!$this->nodeTypeResolver->isMethodStaticCallOrClassMethodObjectType($node, new \PHPStan\Type\ObjectType('TYPO3\\CMS\\Core\\Utility\\GeneralUtility'))) {
            return null;
        }
        if (!$this->isName($node->name, 'rmFromList')) {
            return null;
        }
        $explodeFuncCall = $this->nodeFactory->createFuncCall('explode', [',', $node->args[1]]);
        $itemVariable = new \PhpParser\Node\Expr\Variable('item');
        $stmts = [new \PhpParser\Node\Stmt\Return_(new \PhpParser\Node\Expr\BinaryOp\Equal(new \PhpParser\Node\Expr\Variable('element'), $itemVariable))];
        $closureFunction = $this->anonymousFunctionFactory->create([new \PhpParser\Node\Param($itemVariable)], $stmts, null);
        $arrayFilterFuncCall = $this->nodeFactory->createFuncCall('array_filter', [$explodeFuncCall, $closureFunction]);
        return $this->nodeFactory->createFuncCall('implode', [',', $arrayFilterFuncCall]);
    }
    /**
     * @codeCoverageIgnore
     */
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Use native php functions instead of GeneralUtility::rmFromList', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
use TYPO3\CMS\Core\Utility\GeneralUtility;

$element = '1';
$list = '1,2,3';

$newList = GeneralUtility::rmFromList($element, $list);
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$element = '1';
$list = '1,2,3';
$newList = implode(',', array_filter(explode(',', $list), function($item) use($element) {
    return $element == $item;
}));
CODE_SAMPLE
)]);
    }
}
