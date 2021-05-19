<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\Rector\v9\v0;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\BinaryOp\Mul;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Scalar\String_;
use Rector\Core\Rector\AbstractRector;
use Ssch\TYPO3Rector\Helper\Typo3NodeResolver;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/9.0/Breaking-82893-RemoveGlobalVariablePARSETIME_START.html
 * @see \Ssch\TYPO3Rector\Tests\Rector\v9\v0\SubstituteConstantParsetimeStartRector\SubstituteConstantParsetimeStartRectorTest
 */
final class SubstituteConstantParsetimeStartRector extends \Rector\Core\Rector\AbstractRector
{
    /**
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
        return [\PhpParser\Node\Expr::class];
    }
    /**
     * @param Expr $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if (!$this->typo3NodeResolver->isTypo3Global($node, \Ssch\TYPO3Rector\Helper\Typo3NodeResolver::PARSETIME_START)) {
            return null;
        }
        return $this->nodeFactory->createFuncCall('round', [new \PhpParser\Node\Expr\BinaryOp\Mul(new \PhpParser\Node\Expr\ArrayDimFetch(new \PhpParser\Node\Expr\ArrayDimFetch(new \PhpParser\Node\Expr\Variable('GLOBALS'), new \PhpParser\Node\Scalar\String_('TYPO3_MISC')), new \PhpParser\Node\Scalar\String_('microtime_start')), new \PhpParser\Node\Scalar\LNumber(1000))]);
    }
    /**
     * @codeCoverageIgnore
     */
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Substitute $GLOBALS[\'PARSETIME_START\'] with round($GLOBALS[\'TYPO3_MISC\'][\'microtime_start\'] * 1000)', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
$parseTime = $GLOBALS['PARSETIME_START'];
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$parseTime = round($GLOBALS['TYPO3_MISC']['microtime_start'] * 1000);
CODE_SAMPLE
)]);
    }
}
