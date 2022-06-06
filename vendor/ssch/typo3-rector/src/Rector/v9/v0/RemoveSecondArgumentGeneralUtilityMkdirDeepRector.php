<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Ssch\TYPO3Rector\Rector\v9\v0;

use RectorPrefix20220606\PhpParser\Node;
use RectorPrefix20220606\PhpParser\Node\Expr\BinaryOp\Concat;
use RectorPrefix20220606\PhpParser\Node\Expr\StaticCall;
use RectorPrefix20220606\PHPStan\Type\ObjectType;
use RectorPrefix20220606\Rector\Core\Rector\AbstractRector;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/9.0/Deprecation-82702-SecondArgumentOfGeneralUtilitymkdir_deep.html
 * @see \Ssch\TYPO3Rector\Tests\Rector\v9\v0\RemoveSecondArgumentGeneralUtilityMkdirDeepRector\RemoveSecondArgumentGeneralUtilityMkdirDeepRectorTest
 */
final class RemoveSecondArgumentGeneralUtilityMkdirDeepRector extends AbstractRector
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
        if (!$this->isName($node->name, 'mkdir_deep')) {
            return null;
        }
        $arguments = $node->args;
        if (\count($arguments) <= 1) {
            return null;
        }
        $concat = new Concat($node->args[0]->value, $node->args[1]->value);
        return $this->nodeFactory->createStaticCall('TYPO3\\CMS\\Core\\Utility\\GeneralUtility', 'mkdir_deep', [$concat]);
    }
    /**
     * @codeCoverageIgnore
     */
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Remove second argument of GeneralUtility::mkdir_deep()', [new CodeSample(<<<'CODE_SAMPLE'
GeneralUtility::mkdir_deep(PATH_site . 'typo3temp/', 'myfolder');
CODE_SAMPLE
, <<<'CODE_SAMPLE'
GeneralUtility::mkdir_deep(PATH_site . 'typo3temp/' . 'myfolder');
CODE_SAMPLE
)]);
    }
}
