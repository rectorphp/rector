<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\Rector\v8\v0;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticCall;
use PHPStan\Type\ObjectType;
use Rector\Core\Rector\AbstractRector;
use Ssch\TYPO3Rector\Helper\Typo3NodeResolver;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/8.0/Deprecation-73482-LANG-csConvObjAndLANG-parserFactory.html
 * @see \Ssch\TYPO3Rector\Tests\Rector\v8\v0\RemoveLangCsConvObjAndParserFactoryRector\RemoveLangCsConvObjAndParserFactoryRectorTest
 */
final class RemoveLangCsConvObjAndParserFactoryRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @readonly
     * @var \Ssch\TYPO3Rector\Helper\Typo3NodeResolver
     */
    private $typo3NodeResolver;
    public function __construct(\Ssch\TYPO3Rector\Helper\Typo3NodeResolver $typo3NodeResolver)
    {
        $this->typo3NodeResolver = $typo3NodeResolver;
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Expr\MethodCall::class, \PhpParser\Node\Expr\PropertyFetch::class];
    }
    /**
     * @param MethodCall|PropertyFetch $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if ($this->shouldSkip($node)) {
            return null;
        }
        if ($this->isLanguageServiceCall($node)) {
            return $this->refactorLanguageServiceCall($node);
        }
        return null;
    }
    /**
     * @codeCoverageIgnore
     */
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Remove CsConvObj and ParserFactory from LanguageService::class and $GLOBALS[\'lang\']', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
$languageService = GeneralUtility::makeInstance(LanguageService::class);
$charsetConverter = $languageService->csConvObj;
$Localization = $languageService->parserFactory();
$charsetConverterGlobals = $GLOBALS['LANG']->csConvObj;
$LocalizationGlobals = $GLOBALS['LANG']->parserFactory();
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$languageService = GeneralUtility::makeInstance(LanguageService::class);
$charsetConverter = GeneralUtility::makeInstance(CharsetConverter::class);
$Localization = GeneralUtility::makeInstance(LocalizationFactory::class);
$charsetConverterGlobals = GeneralUtility::makeInstance(CharsetConverter::class);
$LocalizationGlobals = GeneralUtility::makeInstance(LocalizationFactory::class);
CODE_SAMPLE
)]);
    }
    /**
     * @param \PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\PropertyFetch $node
     */
    private function shouldSkip($node) : bool
    {
        if ($this->isLanguageServiceCall($node)) {
            return \false;
        }
        return $node instanceof \PhpParser\Node\Expr\PropertyFetch;
    }
    private function isLanguageServiceCall(\PhpParser\Node $node) : bool
    {
        if (!(\property_exists($node, 'var') && null !== $node->var)) {
            return \false;
        }
        if ($this->isObjectType($node->var, new \PHPStan\Type\ObjectType('TYPO3\\CMS\\Core\\Localization\\LanguageService'))) {
            return \true;
        }
        if ($this->typo3NodeResolver->isPropertyFetchOnAnyPropertyOfGlobals($node, \Ssch\TYPO3Rector\Helper\Typo3NodeResolver::LANG)) {
            return \true;
        }
        return $this->typo3NodeResolver->isAnyMethodCallOnGlobals($node, \Ssch\TYPO3Rector\Helper\Typo3NodeResolver::LANG);
    }
    private function refactorLanguageServiceCall(\PhpParser\Node $node) : ?\PhpParser\Node\Expr\StaticCall
    {
        if (!(\property_exists($node, 'name') && null !== $node->name)) {
            return null;
        }
        $nodeName = $this->getName($node->name);
        if (null === $nodeName) {
            return null;
        }
        if ($this->isName($node->name, 'csConvObj')) {
            return $this->nodeFactory->createStaticCall('TYPO3\\CMS\\Core\\Utility\\GeneralUtility', 'makeInstance', [$this->nodeFactory->createClassConstReference('TYPO3\\CMS\\Core\\Charset\\CharsetConverter')]);
        }
        if ($this->isName($node->name, 'parserFactory')) {
            return $this->nodeFactory->createStaticCall('TYPO3\\CMS\\Core\\Utility\\GeneralUtility', 'makeInstance', [$this->nodeFactory->createClassConstReference('TYPO3\\CMS\\Core\\Localization\\LocalizationFactory')]);
        }
        return null;
    }
}
