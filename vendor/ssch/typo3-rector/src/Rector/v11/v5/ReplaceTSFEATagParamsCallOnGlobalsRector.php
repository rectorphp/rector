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
final class ReplaceTSFEATagParamsCallOnGlobalsRector extends AbstractRector
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
        $propertyFetch = $this->nodeFactory->createPropertyFetch(new ArrayDimFetch(new Variable('GLOBALS'), new String_(Typo3NodeResolver::TYPO_SCRIPT_FRONTEND_CONTROLLER)), 'config');
        return new Coalesce(new ArrayDimFetch(new ArrayDimFetch($propertyFetch, new String_('config')), new String_('ATagParams')), new String_(''));
    }
    /**
     * @codeCoverageIgnore
     */
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Replaces all direct calls to $GLOBALS[\'TSFE\']->ATagParams.', [new CodeSample(<<<'CODE_SAMPLE'
$foo = $GLOBALS['TSFE']->ATagParams;
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$foo = $GLOBALS['TSFE']->config['config']['ATagParams'] ?? '';
CODE_SAMPLE
)]);
    }
    private function shouldSkip(PropertyFetch $propertyFetch) : bool
    {
        $parentNode = $propertyFetch->getAttribute(AttributeKey::PARENT_NODE);
        // Check if we have an assigment to the property, if so do not change it
        if ($parentNode instanceof Assign && $parentNode->var instanceof PropertyFetch) {
            return \true;
        }
        if (!$this->isName($propertyFetch->name, 'ATagParams')) {
            return \true;
        }
        if ($this->isObjectType($propertyFetch->var, new ObjectType('TYPO3\\CMS\\Frontend\\Controller\\TypoScriptFrontendController'))) {
            return \false;
        }
        return !$this->typo3NodeResolver->isPropertyFetchOnAnyPropertyOfGlobals($propertyFetch, Typo3NodeResolver::TYPO_SCRIPT_FRONTEND_CONTROLLER);
    }
}
