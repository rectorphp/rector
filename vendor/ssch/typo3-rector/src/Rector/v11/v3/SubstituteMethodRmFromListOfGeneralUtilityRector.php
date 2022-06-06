<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Ssch\TYPO3Rector\Rector\v11\v3;

use RectorPrefix20220606\PhpParser\Node;
use RectorPrefix20220606\PhpParser\Node\Expr\BinaryOp\Equal;
use RectorPrefix20220606\PhpParser\Node\Expr\StaticCall;
use RectorPrefix20220606\PhpParser\Node\Expr\Variable;
use RectorPrefix20220606\PhpParser\Node\Param;
use RectorPrefix20220606\PhpParser\Node\Stmt\Return_;
use RectorPrefix20220606\PHPStan\Type\ObjectType;
use RectorPrefix20220606\Rector\Core\Rector\AbstractRector;
use RectorPrefix20220606\Rector\Php72\NodeFactory\AnonymousFunctionFactory;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/11.3/Deprecation-94311-DeprecatedGeneralUtilityrmFromList.html
 * @see \Ssch\TYPO3Rector\Tests\Rector\v11\v3\SubstituteMethodRmFromListOfGeneralUtilityRector\SubstituteMethodRmFromListOfGeneralUtilityRectorTest
 */
final class SubstituteMethodRmFromListOfGeneralUtilityRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\Php72\NodeFactory\AnonymousFunctionFactory
     */
    private $anonymousFunctionFactory;
    public function __construct(AnonymousFunctionFactory $anonymousFunctionFactory)
    {
        $this->anonymousFunctionFactory = $anonymousFunctionFactory;
    }
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
        if (!$this->nodeTypeResolver->isMethodStaticCallOrClassMethodObjectType($node, new ObjectType('TYPO3\\CMS\\Core\\Utility\\GeneralUtility'))) {
            return null;
        }
        if (!$this->isName($node->name, 'rmFromList')) {
            return null;
        }
        $explodeFuncCall = $this->nodeFactory->createFuncCall('explode', [',', $node->args[1]]);
        $itemVariable = new Variable('item');
        $stmts = [new Return_(new Equal(new Variable('element'), $itemVariable))];
        $closureFunction = $this->anonymousFunctionFactory->create([new Param($itemVariable)], $stmts, null);
        $arrayFilterFuncCall = $this->nodeFactory->createFuncCall('array_filter', [$explodeFuncCall, $closureFunction]);
        return $this->nodeFactory->createFuncCall('implode', [',', $arrayFilterFuncCall]);
    }
    /**
     * @codeCoverageIgnore
     */
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Use native php functions instead of GeneralUtility::rmFromList', [new CodeSample(<<<'CODE_SAMPLE'
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
