<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\Rector\v9\v3;

use PhpParser\Node;
use PhpParser\Node\Expr\PropertyFetch;
use PHPStan\Type\ObjectType;
use Rector\Core\Rector\AbstractRector;
use Ssch\TYPO3Rector\Helper\Typo3NodeResolver;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/9.3/Deprecation-84984-ProtectedUserTSconfigPropertiesInBackendUserAuthentication.html
 * @see \Ssch\TYPO3Rector\Tests\Rector\v9\v3\PropertyUserTsToMethodGetTsConfigOfBackendUserAuthenticationRector\PropertyUserTsToMethodGetTsConfigOfBackendUserAuthenticationRectorTest
 */
final class PropertyUserTsToMethodGetTsConfigOfBackendUserAuthenticationRector extends \Rector\Core\Rector\AbstractRector
{
    /**
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
        return [\PhpParser\Node\Expr\PropertyFetch::class];
    }
    /**
     * @param PropertyFetch $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if ($this->shouldSkip($node)) {
            return null;
        }
        if (!$this->isName($node->name, 'userTS')) {
            return null;
        }
        return $this->nodeFactory->createMethodCall($node->var, 'getTSConfig');
    }
    /**
     * @codeCoverageIgnore
     */
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Use method getTSConfig instead of property userTS', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
if(is_array($GLOBALS['BE_USER']->userTS['tx_news.']) && $GLOBALS['BE_USER']->userTS['tx_news.']['singleCategoryAcl'] === '1') {
    return true;
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
if(is_array($GLOBALS['BE_USER']->getTSConfig()['tx_news.']) && $GLOBALS['BE_USER']->getTSConfig()['tx_news.']['singleCategoryAcl'] === '1') {
    return true;
}
CODE_SAMPLE
)]);
    }
    private function shouldSkip(\PhpParser\Node\Expr\PropertyFetch $node) : bool
    {
        if ($this->typo3NodeResolver->isPropertyFetchOnAnyPropertyOfGlobals($node, \Ssch\TYPO3Rector\Helper\Typo3NodeResolver::BACKEND_USER)) {
            return \false;
        }
        return !$this->isObjectType($node->var, new \PHPStan\Type\ObjectType('TYPO3\\CMS\\Core\\Authentication\\BackendUserAuthentication'));
    }
}
