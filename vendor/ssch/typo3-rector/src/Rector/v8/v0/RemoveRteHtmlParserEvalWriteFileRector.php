<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\Rector\v8\v0;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PHPStan\Type\ObjectType;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/8.0/Breaking-72384-RemovedDeprecatedCodeFromHtmlParser.html
 * @see \Ssch\TYPO3Rector\Tests\Rector\v8\v0\CoreRector\Html\RemoveRteHtmlParserEvalWriteFileRectorTest
 */
final class RemoveRteHtmlParserEvalWriteFileRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Expr\MethodCall::class, \PhpParser\Node\Expr\StaticCall::class];
    }
    /**
     * @param StaticCall|MethodCall $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if (!$this->nodeTypeResolver->isMethodStaticCallOrClassMethodObjectType($node, new \PHPStan\Type\ObjectType('TYPO3\\CMS\\Core\\Html\\RteHtmlParser'))) {
            return null;
        }
        if ($this->isName($node->name, 'evalWriteFile')) {
            $methodName = $this->getName($node->name);
            if (null === $methodName) {
                return null;
            }
            try {
                $this->removeNode($node);
                return $node;
            } catch (\Rector\Core\Exception\ShouldNotHappenException $exception) {
                $parentNode = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
                $this->removeNode($parentNode);
                return $node;
            }
        }
        return null;
    }
    /**
     * @codeCoverageIgnore
     */
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('remove evalWriteFile method from RteHtmlparser.', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
use TYPO3\CMS\Core\Html\RteHtmlParser;

final class RteHtmlParserRemovedMethods
{

    public function doSomething(): void
    {
        $rtehtmlparser = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(RteHtmlParser::class);
        $rtehtmlparser->evalWriteFile();
    }

}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use TYPO3\CMS\Core\Html\RteHtmlParser;

final class RteHtmlParserRemovedMethods
{

    public function doSomething(): void
    {
        $rtehtmlparser = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(RteHtmlParser::class);
    }

}
CODE_SAMPLE
)]);
    }
}
