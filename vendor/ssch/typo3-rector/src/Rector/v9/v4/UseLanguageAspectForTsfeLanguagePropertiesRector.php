<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\Rector\v9\v4;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\PropertyFetch;
use PHPStan\Type\ObjectType;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Ssch\TYPO3Rector\Helper\Typo3NodeResolver;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/9.4/Deprecation-85543-Language-relatedPropertiesInTypoScriptFrontendControllerAndPageRepository.html
 * @see \Ssch\TYPO3Rector\Tests\Rector\v9\v4\UseLanguageAspectForTsfeLanguagePropertiesRector\UseLanguageAspectForTsfeLanguagePropertiesRectorTest
 */
final class UseLanguageAspectForTsfeLanguagePropertiesRector extends AbstractRector
{
    /**
     * @var array<string, string>
     */
    private const NODE_NAME_MAPPING = ['sys_language_uid' => 'id', 'sys_language_content' => 'contentId', 'sys_language_contentOL' => 'legacyOverlayType', 'sys_language_mode' => 'legacyLanguageMode'];
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
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [PropertyFetch::class];
    }
    /**
     * @param PropertyFetch $node
     */
    public function refactor(Node $node) : ?Node
    {
        if ($this->shouldSkip($node)) {
            return null;
        }
        if (!$this->isNames($node->name, ['sys_language_uid', 'sys_language_content', 'sys_language_contentOL', 'sys_language_mode'])) {
            return null;
        }
        $parentNode = $node->getAttribute(AttributeKey::PARENT_NODE);
        // Check if we have an assigment to the property, if so do not change it
        if ($parentNode instanceof Assign && $parentNode->var instanceof PropertyFetch) {
            return null;
        }
        $nodeName = $this->getName($node->name);
        if (null === $nodeName) {
            return null;
        }
        $property = self::NODE_NAME_MAPPING[$nodeName];
        return $this->nodeFactory->createMethodCall($this->nodeFactory->createStaticCall('TYPO3\\CMS\\Core\\Utility\\GeneralUtility', 'makeInstance', [$this->nodeFactory->createClassConstReference('TYPO3\\CMS\\Core\\Context\\Context')]), 'getPropertyFromAspect', ['language', $property]);
    }
    /**
     * @codeCoverageIgnore
     */
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Use LanguageAspect instead of language properties of TSFE', [new CodeSample(<<<'CODE_SAMPLE'
$languageUid = $GLOBALS['TSFE']->sys_language_uid;
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Utility\GeneralUtility;
$languageUid = GeneralUtility::makeInstance(Context::class)->getPropertyFromAspect('language', 'id');
CODE_SAMPLE
)]);
    }
    private function shouldSkip(PropertyFetch $propertyFetch) : bool
    {
        if ($this->isObjectType($propertyFetch->var, new ObjectType('TYPO3\\CMS\\Frontend\\Controller\\TypoScriptFrontendController'))) {
            return \false;
        }
        if ($this->typo3NodeResolver->isPropertyFetchOnAnyPropertyOfGlobals($propertyFetch, Typo3NodeResolver::TYPO_SCRIPT_FRONTEND_CONTROLLER)) {
            return \false;
        }
        return !$this->typo3NodeResolver->isPropertyFetchOnParentVariableOfTypeTypoScriptFrontendController($propertyFetch);
    }
}
