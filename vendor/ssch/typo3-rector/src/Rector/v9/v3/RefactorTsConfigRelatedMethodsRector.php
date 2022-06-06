<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Ssch\TYPO3Rector\Rector\v9\v3;

use RectorPrefix20220606\PhpParser\Node;
use RectorPrefix20220606\PhpParser\Node\Expr;
use RectorPrefix20220606\PhpParser\Node\Expr\ArrayDimFetch;
use RectorPrefix20220606\PhpParser\Node\Expr\BinaryOp\Coalesce;
use RectorPrefix20220606\PhpParser\Node\Expr\Cast;
use RectorPrefix20220606\PhpParser\Node\Expr\Cast\Array_;
use RectorPrefix20220606\PhpParser\Node\Expr\Cast\Bool_;
use RectorPrefix20220606\PhpParser\Node\Expr\Cast\Int_;
use RectorPrefix20220606\PhpParser\Node\Expr\Cast\String_ as StringCast;
use RectorPrefix20220606\PhpParser\Node\Expr\MethodCall;
use RectorPrefix20220606\PhpParser\Node\Scalar\LNumber;
use RectorPrefix20220606\PhpParser\Node\Scalar\String_;
use RectorPrefix20220606\PHPStan\Type\ObjectType;
use RectorPrefix20220606\Rector\Core\Rector\AbstractRector;
use RectorPrefix20220606\Rector\Core\ValueObject\PhpVersionFeature;
use RectorPrefix20220606\Rector\NodeTypeResolver\Node\AttributeKey;
use RectorPrefix20220606\Rector\VersionBonding\Contract\MinPhpVersionInterface;
use RectorPrefix20220606\Ssch\TYPO3Rector\Helper\Typo3NodeResolver;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/9.3/Deprecation-84993-DeprecateSomeTSconfigRelatedMethods.html
 * @see \Ssch\TYPO3Rector\Tests\Rector\v9\v3\RefactorTsConfigRelatedMethodsRector\RefactorTsConfigRelatedMethodsRectorTest
 */
final class RefactorTsConfigRelatedMethodsRector extends AbstractRector implements MinPhpVersionInterface
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
        return new RuleDefinition('Refactor TSconfig related methods', [new CodeSample(<<<'CODE_SAMPLE'
$hasFilterBox = !$GLOBALS['BE_USER']->getTSConfigVal('options.pageTree.hideFilter');
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$hasFilterBox = !($GLOBALS['BE_USER']->getTSConfig()['options.']['pageTree.']['hideFilter.'] ?? null);
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
        if (!$this->isNames($node->name, ['getTSConfigVal', 'getTSConfigProp', 'getTSConfig'])) {
            return null;
        }
        if (!isset($node->args[0])) {
            return null;
        }
        $value = $this->valueResolver->getValue($node->args[0]->value);
        if (null === $value) {
            return null;
        }
        if (!\is_string($value) || '' === $value) {
            return null;
        }
        $configuration = $this->createConfiguration($value);
        $newArrayDimFetch = $this->nodeFactory->createMethodCall($node->var, 'getTSConfig');
        $parentNode = $node->getAttribute(AttributeKey::PARENT_NODE);
        $defaultValueNode = $this->nodeFactory->createNull();
        if ($parentNode instanceof Cast) {
            $defaultValueNode = $this->transformToSpecificCast($parentNode);
        }
        foreach ($configuration as $key) {
            $newArrayDimFetch = new ArrayDimFetch($newArrayDimFetch, new String_(\sprintf('%s.', $key)));
        }
        return new Coalesce($newArrayDimFetch, $defaultValueNode);
    }
    public function provideMinPhpVersion() : int
    {
        return PhpVersionFeature::NULL_COALESCE;
    }
    /**
     * @param MethodCall $node
     */
    private function shouldSkip(Node $node) : bool
    {
        if ($this->typo3NodeResolver->isMethodCallOnBackendUser($node)) {
            return \false;
        }
        return !$this->isObjectType($node->var, new ObjectType('TYPO3\\CMS\\Core\\Authentication\\BackendUserAuthentication'));
    }
    /**
     * @return string[]
     */
    private function createConfiguration(string $objectString) : array
    {
        return \explode('.', $objectString);
    }
    private function transformToSpecificCast(Cast $node) : Expr
    {
        if ($node instanceof Array_) {
            return $this->nodeFactory->createArray([]);
        }
        if ($node instanceof StringCast) {
            return new String_('');
        }
        if ($node instanceof Bool_) {
            return $this->nodeFactory->createFalse();
        }
        if ($node instanceof Int_) {
            return new LNumber(0);
        }
        return $this->nodeFactory->createNull();
    }
}
