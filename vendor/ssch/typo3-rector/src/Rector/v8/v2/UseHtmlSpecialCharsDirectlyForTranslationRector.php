<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\Rector\v8\v2;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Type\ObjectType;
use Rector\Core\Rector\AbstractRector;
use Ssch\TYPO3Rector\Helper\Typo3NodeResolver;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/8.2/Deprecation-71917-DeprecateTheArgumentHscForGetLLGetLLLAndSL.html
 * @see \Ssch\TYPO3Rector\Tests\Rector\v8\v2\UseHtmlSpecialCharsDirectlyForTranslationRector\UseHtmlSpecialCharsDirectlyForTranslationRectorTest
 */
final class UseHtmlSpecialCharsDirectlyForTranslationRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Ssch\TYPO3Rector\Helper\Typo3NodeResolver
     */
    private $typo3NodeResolver;
    public function __construct(Typo3NodeResolver $typo3NodeResolver)
    {
        $this->typo3NodeResolver = $typo3NodeResolver;
    }
    /**
     * @codeCoverageIgnore
     */
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('htmlspecialchars directly to properly escape the content.', [new CodeSample(<<<'CODE_SAMPLE'
use TYPO3\CMS\Frontend\Plugin\AbstractPlugin;
class MyPlugin extends AbstractPlugin
{
    public function translate($hsc): void
    {
        $translation = $this->pi_getLL('label', '', true);
        $translation2 = $this->pi_getLL('label', '', false);
        $translation3 = $this->pi_getLL('label', '', $hsc);
        $translation9 = $GLOBALS['LANG']->sL('foobar', true);
        $translation10 = $GLOBALS['LANG']->sL('foobar', false);
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use TYPO3\CMS\Frontend\Plugin\AbstractPlugin;
class MyPlugin extends AbstractPlugin
{
    public function translate($hsc): void
    {
        $translation = htmlspecialchars($this->pi_getLL('label', ''));
        $translation2 = $this->pi_getLL('label', '');
        $translation3 = $this->pi_getLL('label', '', $hsc);
        $translation9 = htmlspecialchars($GLOBALS['LANG']->sL('foobar'));
        $translation10 = $GLOBALS['LANG']->sL('foobar');
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [MethodCall::class];
    }
    /**
     * @param MethodCall $node
     */
    public function refactor(Node $node) : ?Node
    {
        if ($this->shouldSkip($node)) {
            return null;
        }
        if ($this->isLanguageServiceCall($node)) {
            return $this->refactorLanguageServiceCall($node);
        }
        return $this->refactorAbstractPluginCall($node);
    }
    private function shouldSkip(MethodCall $methodCall) : bool
    {
        if ($this->isLanguageServiceCall($methodCall)) {
            return \false;
        }
        return !$this->nodeTypeResolver->isMethodStaticCallOrClassMethodObjectType($methodCall, new ObjectType('TYPO3\\CMS\\Frontend\\Plugin\\AbstractPlugin'));
    }
    private function isLanguageServiceCall(MethodCall $methodCall) : bool
    {
        if ($this->nodeTypeResolver->isMethodStaticCallOrClassMethodObjectType($methodCall, new ObjectType('TYPO3\\CMS\\Lang\\LanguageService'))) {
            return \true;
        }
        return $this->typo3NodeResolver->isAnyMethodCallOnGlobals($methodCall, Typo3NodeResolver::LANG);
    }
    private function refactorAbstractPluginCall(MethodCall $methodCall) : ?Node
    {
        if (!$this->isName($methodCall->name, 'pi_getLL')) {
            return null;
        }
        return $this->refactorToHtmlSpecialChars($methodCall, 2);
    }
    private function refactorLanguageServiceCall(MethodCall $methodCall) : ?Node
    {
        if (!$this->isNames($methodCall->name, ['sL', 'getLL', 'getLLL'])) {
            return null;
        }
        if ($this->isName($methodCall->name, 'getLLL')) {
            return $this->refactorToHtmlSpecialChars($methodCall, 2);
        }
        return $this->refactorToHtmlSpecialChars($methodCall, 1);
    }
    private function refactorToHtmlSpecialChars(MethodCall $methodCall, int $argumentPosition) : ?Node
    {
        if (!isset($methodCall->args[$argumentPosition])) {
            return null;
        }
        $hsc = $this->valueResolver->getValue($methodCall->args[$argumentPosition]->value);
        if (null === $hsc) {
            return null;
        }
        // If you don´t unset it you will end up in an infinite loop here
        unset($methodCall->args[$argumentPosition]);
        if (\false === $hsc) {
            return null;
        }
        return $this->nodeFactory->createFuncCall('htmlspecialchars', [$methodCall]);
    }
}
