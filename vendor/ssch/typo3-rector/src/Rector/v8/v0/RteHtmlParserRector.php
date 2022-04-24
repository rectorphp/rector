<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\Rector\v8\v0;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Scalar\String_;
use PHPStan\Type\ObjectType;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/8.0/Breaking-72686-RemovedRteHtmlParserMethods.html
 * @see \Ssch\TYPO3Rector\Tests\Rector\v8\v0\RteHtmlParserRector\RteHtmlParserRectorTest
 */
final class RteHtmlParserRector extends \Rector\Core\Rector\AbstractRector
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
        if ($this->shouldSkip($node)) {
            return null;
        }
        if ($this->isNames($node->name, ['HTMLcleaner_db', 'getKeepTags'])) {
            return $this->removeSecondArgumentFromMethod($node);
        }
        if ($this->isName($node->name, 'siteUrl')) {
            return $this->nodeFactory->createStaticCall('TYPO3\\CMS\\Core\\Utility\\GeneralUtility', 'getIndpEnv', [$this->nodeFactory->createArg(new \PhpParser\Node\Scalar\String_('TYPO3_SITE_URL'))]);
        }
        if ($this->isName($node->name, 'getUrl')) {
            return $this->nodeFactory->createStaticCall('TYPO3\\CMS\\Core\\Utility\\GeneralUtility', 'getUrl', [$node->args[0]]);
        }
        return null;
    }
    /**
     * @codeCoverageIgnore
     */
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Remove second argument of HTMLcleaner_db getKeepTags. Substitute calls for siteUrl getUrl', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
            use TYPO3\CMS\Core\Html\RteHtmlParser;

            $rteHtmlParser = new RteHtmlParser();
            $rteHtmlParser->HTMLcleaner_db('arg1', 'arg2');
            $rteHtmlParser->getKeepTags('arg1', 'arg2');
            $rteHtmlParser->getUrl('http://domain.com');
            $rteHtmlParser->siteUrl();
CODE_SAMPLE
, <<<'CODE_SAMPLE'
            use TYPO3\CMS\Core\Html\RteHtmlParser;
            $rteHtmlParser = new RteHtmlParser();
            $rteHtmlParser->HTMLcleaner_db('arg1');
            $rteHtmlParser->getKeepTags('arg1');
            \TYPO3\CMS\Core\Utility\GeneralUtility::getUrl('http://domain.com');
             \TYPO3\CMS\Core\Utility\GeneralUtility::getIndpEnv('TYPO3_SITE_URL');
CODE_SAMPLE
)]);
    }
    private function removeSecondArgumentFromMethod(\PhpParser\Node\Expr\MethodCall $methodCall) : \PhpParser\Node
    {
        $numberOfArguments = \count($methodCall->args);
        if ($numberOfArguments > 1) {
            unset($methodCall->args[1]);
        }
        return $methodCall;
    }
    private function shouldSkip(\PhpParser\Node\Expr\MethodCall $methodCall) : bool
    {
        return !$this->nodeTypeResolver->isMethodStaticCallOrClassMethodObjectType($methodCall, new \PHPStan\Type\ObjectType('TYPO3\\CMS\\Core\\Html\\RteHtmlParser'));
    }
}
