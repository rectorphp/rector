<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\Rector\v7\v1;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Type\ObjectType;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/7.1/Deprecation-46770-LocalImageProcessorGraphicalFunctions.html
 * @see \Ssch\TYPO3Rector\Tests\Rector\v7\v1\GetTemporaryImageWithTextRector\GetTemporaryImageWithTextRectorTest
 */
final class GetTemporaryImageWithTextRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Expr\MethodCall::class];
    }
    /**
     * @param MethodCall $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if (!$this->nodeTypeResolver->isMethodStaticCallOrClassMethodObjectType($node, new \PHPStan\Type\ObjectType('TYPO3\\CMS\\Core\\Resource\\Processing\\LocalImageProcessor'))) {
            return null;
        }
        if (!$this->isName($node->name, 'getTemporaryImageWithText')) {
            return null;
        }
        return $this->nodeFactory->createMethodCall($this->nodeFactory->createStaticCall('TYPO3\\CMS\\Core\\Utility\\GeneralUtility', 'makeInstance', [$this->nodeFactory->createClassConstReference('TYPO3\\CMS\\Core\\Imaging\\GraphicalFunctions')]), 'getTemporaryImageWithText', $node->args);
    }
    /**
     * @codeCoverageIgnore
     */
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Use GraphicalFunctions->getTemporaryImageWithText instead of LocalImageProcessor->getTemporaryImageWithText', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample('GeneralUtility::makeInstance(LocalImageProcessor::class)->getTemporaryImageWithText("foo", "bar", "baz", "foo")', 'GeneralUtility::makeInstance(GraphicalFunctions::class)->getTemporaryImageWithText("foo", "bar", "baz", "foo")')]);
    }
}
