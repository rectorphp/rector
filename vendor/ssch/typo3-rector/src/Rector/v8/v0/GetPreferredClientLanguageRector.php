<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\Rector\v8\v0;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PHPStan\Type\ObjectType;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/8.0/Deprecation-73511-BrowserLanguageDetectionMovedToLocales.html
 * @see \Ssch\TYPO3Rector\Tests\Rector\v8\v0\GetPreferredClientLanguageRector\GetPreferredClientLanguageRectorTest
 */
final class GetPreferredClientLanguageRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @var string
     */
    private const GET_PREFERRED_CLIENT_LANGUAGE = 'getPreferredClientLanguage';
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
        if (!$this->isCharsetConverterMethodCall($node) && !$this->isCallFromTypoScriptFrontendController($node)) {
            return null;
        }
        return $this->nodeFactory->createMethodCall($this->nodeFactory->createStaticCall('TYPO3\\CMS\\Core\\Utility\\GeneralUtility', 'makeInstance', [$this->nodeFactory->createClassConstReference('TYPO3\\CMS\\Core\\Localization\\Locales')]), self::GET_PREFERRED_CLIENT_LANGUAGE, $node->args);
    }
    /**
     * @codeCoverageIgnore
     */
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Use Locales->getPreferredClientLanguage() instead of CharsetConverter::getPreferredClientLanguage()', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
use TYPO3\CMS\Core\Utility\GeneralUtility;
$preferredLanguage = $GLOBALS['TSFE']->csConvObj->getPreferredClientLanguage(GeneralUtility::getIndpEnv('HTTP_ACCEPT_LANGUAGE'));
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use TYPO3\CMS\Core\Localization\Locales;
use TYPO3\CMS\Core\Utility\GeneralUtility;
$preferredLanguage = GeneralUtility::makeInstance(Locales::class)->getPreferredClientLanguage(GeneralUtility::getIndpEnv('HTTP_ACCEPT_LANGUAGE'));
CODE_SAMPLE
)]);
    }
    private function isCharsetConverterMethodCall(\PhpParser\Node\Expr\MethodCall $node) : bool
    {
        if (!$this->nodeTypeResolver->isMethodStaticCallOrClassMethodObjectType($node, new \PHPStan\Type\ObjectType('TYPO3\\CMS\\Core\\Charset\\CharsetConverter'))) {
            return \false;
        }
        return $this->isName($node->name, self::GET_PREFERRED_CLIENT_LANGUAGE);
    }
    private function isCallFromTypoScriptFrontendController(\PhpParser\Node\Expr\MethodCall $node) : bool
    {
        if (!$node->var instanceof \PhpParser\Node\Expr\PropertyFetch) {
            return \false;
        }
        return $this->isName($node->name, self::GET_PREFERRED_CLIENT_LANGUAGE);
    }
}
