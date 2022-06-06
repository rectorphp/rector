<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\Rector\v12\v0\typo3;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Scalar\String_;
use PHPStan\Type\ObjectType;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/main/en-us/Changelog/12.0/Deprecation-96500-ContentObjectRenderer-getMailTo.html
 * @see \Ssch\TYPO3Rector\Tests\Rector\v12\v0\typo3\ReplaceContentObjectRendererGetMailToWithEmailLinkBuilderRector\ReplaceContentObjectRendererGetMailToWithEmailLinkBuilderRectorTest
 */
final class ReplaceContentObjectRendererGetMailToWithEmailLinkBuilderRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Expr\MethodCall::class];
    }
    /**
     * @param Node\Expr\MethodCall $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if ($this->shouldSkip($node)) {
            return null;
        }
        $arguments = [new \PhpParser\Node\Expr\Cast\String_($node->args[0]->value ?? new \PhpParser\Node\Scalar\String_('')), new \PhpParser\Node\Expr\Cast\String_($node->args[1]->value ?? new \PhpParser\Node\Scalar\String_(''))];
        return $this->nodeFactory->createMethodCall($this->nodeFactory->createStaticCall('TYPO3\\CMS\\Core\\Utility\\GeneralUtility', 'makeInstance', [$this->nodeFactory->createClassConstReference('TYPO3\\CMS\\Frontend\\Typolink\\EmailLinkBuilder'), $node->var, $this->nodeFactory->createMethodCall($node->var, 'getTypoScriptFrontendController')]), 'processEmailLink', $arguments);
    }
    /**
     * @codeCoverageIgnore
     */
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Replace usages of ContentObjectRenderer->getMailTo() with EmailLinkBuilder->processEmailLink()', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
$result = $cObj->getMailTo($mailAddress, $linktxt)
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$result = GeneralUtility::makeInstance(EmailLinkBuilder::class, $cObj, $cObj->getTypoScriptFrontendController())
    ->processEmailLink((string)$mailAddress, (string)$linktxt);
CODE_SAMPLE
)]);
    }
    private function shouldSkip(\PhpParser\Node\Expr\MethodCall $methodCall) : bool
    {
        if (!$this->nodeTypeResolver->isMethodStaticCallOrClassMethodObjectType($methodCall, new \PHPStan\Type\ObjectType('TYPO3\\CMS\\Frontend\\ContentObject\\ContentObjectRenderer'))) {
            return \true;
        }
        return !$this->isName($methodCall->name, 'getMailTo');
    }
}
