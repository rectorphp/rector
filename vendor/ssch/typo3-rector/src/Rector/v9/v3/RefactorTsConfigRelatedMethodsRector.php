<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\Rector\v9\v3;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\BinaryOp\Coalesce;
use PhpParser\Node\Expr\Cast;
use PhpParser\Node\Expr\Cast\Array_;
use PhpParser\Node\Expr\Cast\Bool_;
use PhpParser\Node\Expr\Cast\Int_;
use PhpParser\Node\Expr\Cast\String_ as StringCast;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Scalar\String_;
use PHPStan\Type\ObjectType;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Ssch\TYPO3Rector\Helper\Typo3NodeResolver;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/9.3/Deprecation-84993-DeprecateSomeTSconfigRelatedMethods.html
 * @see \Ssch\TYPO3Rector\Tests\Rector\v9\v3\RefactorTsConfigRelatedMethodsRector\RefactorTsConfigRelatedMethodsRectorTest
 */
final class RefactorTsConfigRelatedMethodsRector extends \Rector\Core\Rector\AbstractRector implements \Rector\VersionBonding\Contract\MinPhpVersionInterface
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
     * @codeCoverageIgnore
     */
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Refactor TSconfig related methods', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
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
        return [\PhpParser\Node\Expr\MethodCall::class];
    }
    /**
     * @param MethodCall $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
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
        $newNode = $this->nodeFactory->createMethodCall($node->var, 'getTSConfig');
        $parentNode = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
        $defaultValueNode = $this->nodeFactory->createNull();
        if ($parentNode instanceof \PhpParser\Node\Expr\Cast) {
            $defaultValueNode = $this->transformToSpecificCast($parentNode);
        }
        foreach ($configuration as $key) {
            $newNode = new \PhpParser\Node\Expr\ArrayDimFetch($newNode, new \PhpParser\Node\Scalar\String_(\sprintf('%s.', $key)));
        }
        return new \PhpParser\Node\Expr\BinaryOp\Coalesce($newNode, $defaultValueNode);
    }
    public function provideMinPhpVersion() : int
    {
        return \Rector\Core\ValueObject\PhpVersionFeature::NULL_COALESCE;
    }
    /**
     * @param MethodCall $node
     */
    private function shouldSkip(\PhpParser\Node $node) : bool
    {
        if ($this->typo3NodeResolver->isMethodCallOnBackendUser($node)) {
            return \false;
        }
        return !$this->isObjectType($node->var, new \PHPStan\Type\ObjectType('TYPO3\\CMS\\Core\\Authentication\\BackendUserAuthentication'));
    }
    /**
     * @return string[]
     */
    private function createConfiguration(string $objectString) : array
    {
        return \explode('.', $objectString);
    }
    private function transformToSpecificCast(\PhpParser\Node\Expr\Cast $node) : \PhpParser\Node\Expr
    {
        if ($node instanceof \PhpParser\Node\Expr\Cast\Array_) {
            return $this->nodeFactory->createArray([]);
        }
        if ($node instanceof \PhpParser\Node\Expr\Cast\String_) {
            return new \PhpParser\Node\Scalar\String_('');
        }
        if ($node instanceof \PhpParser\Node\Expr\Cast\Bool_) {
            return $this->nodeFactory->createFalse();
        }
        if ($node instanceof \PhpParser\Node\Expr\Cast\Int_) {
            return new \PhpParser\Node\Scalar\LNumber(0);
        }
        return $this->nodeFactory->createNull();
    }
}
