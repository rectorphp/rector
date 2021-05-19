<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\Rector\v9\v0;

use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp\Concat;
use PhpParser\Node\Expr\StaticCall;
use PHPStan\Type\ObjectType;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/9.0/Deprecation-82702-SecondArgumentOfGeneralUtilitymkdir_deep.html
 * @see \Ssch\TYPO3Rector\Tests\Rector\v9\v0\RemoveSecondArgumentGeneralUtilityMkdirDeepRector\RemoveSecondArgumentGeneralUtilityMkdirDeepRectorTest
 */
final class RemoveSecondArgumentGeneralUtilityMkdirDeepRector extends \Rector\Core\Rector\AbstractRector
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
        if (!$this->isName($node->name, 'mkdir_deep')) {
            return null;
        }
        $arguments = $node->args;
        if (\count($arguments) <= 1) {
            return null;
        }
        $concat = new \PhpParser\Node\Expr\BinaryOp\Concat($node->args[0]->value, $node->args[1]->value);
        return $this->nodeFactory->createStaticCall('TYPO3\\CMS\\Core\\Utility\\GeneralUtility', 'mkdir_deep', [$concat]);
    }
    /**
     * @codeCoverageIgnore
     */
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Remove second argument of GeneralUtility::mkdir_deep()', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
GeneralUtility::mkdir_deep(PATH_site . 'typo3temp/', 'myfolder');
CODE_SAMPLE
, <<<'CODE_SAMPLE'
GeneralUtility::mkdir_deep(PATH_site . 'typo3temp/' . 'myfolder');
CODE_SAMPLE
)]);
    }
}
