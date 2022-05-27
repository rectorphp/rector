<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\Rector\v8\v1;

use PhpParser\Node;
use PhpParser\Node\Expr\StaticCall;
use PHPStan\Type\ObjectType;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/8.3/Deprecation-76804-DeprecateGeneralUtilitystrtoupperStrtolower.html
 * @see \Ssch\TYPO3Rector\Tests\Rector\v8\v1\GeneralUtilityToUpperAndLowerRector\GeneralUtilityToUpperAndLowerRectorTest
 */
final class GeneralUtilityToUpperAndLowerRector extends \Rector\Core\Rector\AbstractRector
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
        if (!$this->nodeTypeResolver->isMethodStaticCallOrClassMethodObjectType($node, new \PHPStan\Type\ObjectType('TYPO3\\CMS\\Core\\Utility\\GeneralUtility'))) {
            return null;
        }
        if (!$this->isNames($node->name, ['strtoupper', 'strtolower'])) {
            return null;
        }
        $funcCall = 'mb_strtolower';
        if ($this->isName($node->name, 'strtoupper')) {
            $funcCall = 'mb_strtoupper';
        }
        return $this->nodeFactory->createFuncCall($funcCall, [$node->args[0], $this->nodeFactory->createArg('utf-8')]);
    }
    /**
     * @codeCoverageIgnore
     */
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Use mb_strtolower and mb_strtoupper', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
use TYPO3\CMS\Core\Utility\GeneralUtility;

$toUpper = GeneralUtility::strtoupper('foo');
$toLower = GeneralUtility::strtolower('FOO');
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$toUpper = mb_strtoupper('foo', 'utf-8');
$toLower = mb_strtolower('FOO', 'utf-8');
CODE_SAMPLE
)]);
    }
}
