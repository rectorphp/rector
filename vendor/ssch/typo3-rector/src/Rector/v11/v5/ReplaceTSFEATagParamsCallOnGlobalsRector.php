<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\Rector\v11\v5;

use PhpParser\Node;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\BinaryOp\Coalesce;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Scalar\String_;
use PHPStan\Type\ObjectType;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Ssch\TYPO3Rector\Helper\Typo3NodeResolver;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/11.5/Deprecation-95219-TypoScriptFrontendController-ATagParams.html
 * @see \Ssch\TYPO3Rector\Tests\Rector\v11\v5\ReplaceTSFEATagParamsCallOnGlobalsRector\ReplaceTSFEATagParamsCallOnGlobalsRectorTest
 */
final class ReplaceTSFEATagParamsCallOnGlobalsRector extends \Rector\Core\Rector\AbstractRector
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
        $propertyFetch = $this->nodeFactory->createPropertyFetch(new \PhpParser\Node\Expr\ArrayDimFetch(new \PhpParser\Node\Expr\Variable('GLOBALS'), new \PhpParser\Node\Scalar\String_(\Ssch\TYPO3Rector\Helper\Typo3NodeResolver::TYPO_SCRIPT_FRONTEND_CONTROLLER)), 'config');
        return new \PhpParser\Node\Expr\BinaryOp\Coalesce(new \PhpParser\Node\Expr\ArrayDimFetch(new \PhpParser\Node\Expr\ArrayDimFetch($propertyFetch, new \PhpParser\Node\Scalar\String_('config')), new \PhpParser\Node\Scalar\String_('ATagParams')), new \PhpParser\Node\Scalar\String_(''));
    }
    /**
     * @codeCoverageIgnore
     */
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Replaces all direct calls to $GLOBALS[\'TSFE\']->ATagParams.', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
$foo = $GLOBALS['TSFE']->ATagParams;
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$foo = $GLOBALS['TSFE']->config['config']['ATagParams'] ?? '';
CODE_SAMPLE
)]);
    }
    private function shouldSkip(\PhpParser\Node\Expr\PropertyFetch $propertyFetch) : bool
    {
        $parentNode = $propertyFetch->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
        // Check if we have an assigment to the property, if so do not change it
        if ($parentNode instanceof \PhpParser\Node\Expr\Assign && $parentNode->var instanceof \PhpParser\Node\Expr\PropertyFetch) {
            return \true;
        }
        if (!$this->isName($propertyFetch->name, 'ATagParams')) {
            return \true;
        }
        if ($this->isObjectType($propertyFetch->var, new \PHPStan\Type\ObjectType('TYPO3\\CMS\\Frontend\\Controller\\TypoScriptFrontendController'))) {
            return \false;
        }
        return !$this->typo3NodeResolver->isPropertyFetchOnAnyPropertyOfGlobals($propertyFetch, \Ssch\TYPO3Rector\Helper\Typo3NodeResolver::TYPO_SCRIPT_FRONTEND_CONTROLLER);
    }
}
