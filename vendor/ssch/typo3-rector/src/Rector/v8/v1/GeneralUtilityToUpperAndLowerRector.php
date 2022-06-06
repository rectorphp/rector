<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Ssch\TYPO3Rector\Rector\v8\v1;

use RectorPrefix20220606\PhpParser\Node;
use RectorPrefix20220606\PhpParser\Node\Expr\StaticCall;
use RectorPrefix20220606\PHPStan\Type\ObjectType;
use RectorPrefix20220606\Rector\Core\Rector\AbstractRector;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/8.3/Deprecation-76804-DeprecateGeneralUtilitystrtoupperStrtolower.html
 * @see \Ssch\TYPO3Rector\Tests\Rector\v8\v1\GeneralUtilityToUpperAndLowerRector\GeneralUtilityToUpperAndLowerRectorTest
 */
final class GeneralUtilityToUpperAndLowerRector extends AbstractRector
{
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
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Use mb_strtolower and mb_strtoupper', [new CodeSample(<<<'CODE_SAMPLE'
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
