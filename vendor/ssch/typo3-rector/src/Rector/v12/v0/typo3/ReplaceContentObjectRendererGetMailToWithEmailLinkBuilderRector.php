<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Ssch\TYPO3Rector\Rector\v12\v0\typo3;

use RectorPrefix20220606\PhpParser\Node;
use RectorPrefix20220606\PhpParser\Node\Expr\MethodCall;
use RectorPrefix20220606\PhpParser\Node\Scalar\String_;
use RectorPrefix20220606\PHPStan\Type\ObjectType;
use RectorPrefix20220606\Rector\Core\Rector\AbstractRector;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/main/en-us/Changelog/12.0/Deprecation-96500-ContentObjectRenderer-getMailTo.html
 * @see \Ssch\TYPO3Rector\Tests\Rector\v12\v0\typo3\ReplaceContentObjectRendererGetMailToWithEmailLinkBuilderRector\ReplaceContentObjectRendererGetMailToWithEmailLinkBuilderRectorTest
 */
final class ReplaceContentObjectRendererGetMailToWithEmailLinkBuilderRector extends AbstractRector
{
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [MethodCall::class];
    }
    /**
     * @param Node\Expr\MethodCall $node
     */
    public function refactor(Node $node) : ?Node
    {
        if ($this->shouldSkip($node)) {
            return null;
        }
        $arguments = [new Node\Expr\Cast\String_($node->args[0]->value ?? new String_('')), new Node\Expr\Cast\String_($node->args[1]->value ?? new String_(''))];
        return $this->nodeFactory->createMethodCall($this->nodeFactory->createStaticCall('TYPO3\\CMS\\Core\\Utility\\GeneralUtility', 'makeInstance', [$this->nodeFactory->createClassConstReference('TYPO3\\CMS\\Frontend\\Typolink\\EmailLinkBuilder'), $node->var, $this->nodeFactory->createMethodCall($node->var, 'getTypoScriptFrontendController')]), 'processEmailLink', $arguments);
    }
    /**
     * @codeCoverageIgnore
     */
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Replace usages of ContentObjectRenderer->getMailTo() with EmailLinkBuilder->processEmailLink()', [new CodeSample(<<<'CODE_SAMPLE'
$result = $cObj->getMailTo($mailAddress, $linktxt)
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$result = GeneralUtility::makeInstance(EmailLinkBuilder::class, $cObj, $cObj->getTypoScriptFrontendController())
    ->processEmailLink((string)$mailAddress, (string)$linktxt);
CODE_SAMPLE
)]);
    }
    private function shouldSkip(MethodCall $methodCall) : bool
    {
        if (!$this->nodeTypeResolver->isMethodStaticCallOrClassMethodObjectType($methodCall, new ObjectType('TYPO3\\CMS\\Frontend\\ContentObject\\ContentObjectRenderer'))) {
            return \true;
        }
        return !$this->isName($methodCall->name, 'getMailTo');
    }
}
