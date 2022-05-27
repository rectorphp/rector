<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\Rector\v10\v4;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Scalar\String_;
use PHPStan\Type\ObjectType;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/10.0/Deprecation-87200-EmailFinisherFormatContants.html
 * @see \Ssch\TYPO3Rector\Tests\Rector\v10\v4\RemoveFormatConstantsEmailFinisherRector\RemoveFormatConstantsEmailFinisherRectorTest
 */
final class RemoveFormatConstantsEmailFinisherRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @var string
     */
    private const FORMAT_HTML = 'FORMAT_HTML';
    /**
     * @var string
     */
    private const FORMAT = 'format';
    /**
     * @var string
     */
    private const ADD_HTML_PART = 'addHtmlPart';
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Expr\ClassConstFetch::class];
    }
    /**
     * @param ClassConstFetch $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if (!$this->isObjectType($node->class, new \PHPStan\Type\ObjectType('TYPO3\\CMS\\Form\\Domain\\Finishers\\EmailFinisher'))) {
            return null;
        }
        if (!$this->isNames($node->name, [self::FORMAT_HTML, 'FORMAT_PLAINTEXT'])) {
            return null;
        }
        $parent = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
        if ($parent instanceof \PhpParser\Node\Arg) {
            $this->refactorSetOptionMethodCall($parent, $node);
        }
        if ($parent instanceof \PhpParser\Node\Expr\ArrayItem) {
            $this->refactorArrayItemOption($parent, $node);
        }
        if ($parent instanceof \PhpParser\Node\Expr\Assign) {
            $this->refactorOptionAssignment($parent, $node);
        }
        if ($parent instanceof \PhpParser\Node\Expr\BinaryOp\Identical) {
            $this->refactorCondition($parent, $node);
        }
        return null;
    }
    /**
     * @codeCoverageIgnore
     */
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Remove constants FORMAT_PLAINTEXT and FORMAT_HTML of class TYPO3\\CMS\\Form\\Domain\\Finishers\\EmailFinisher', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
$this->setOption(self::FORMAT, EmailFinisher::FORMAT_HTML);
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$this->setOption('addHtmlPart', true);
CODE_SAMPLE
)]);
    }
    private function refactorSetOptionMethodCall(\PhpParser\Node\Arg $parent, \PhpParser\Node\Expr\ClassConstFetch $node) : void
    {
        $parent = $parent->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
        if (!$parent instanceof \PhpParser\Node\Expr\MethodCall) {
            return;
        }
        if (!$this->isName($parent->name, 'setOption')) {
            return;
        }
        if (!$this->valueResolver->isValue($parent->args[0]->value, self::FORMAT)) {
            return;
        }
        $addHtmlPart = $this->isName($node->name, self::FORMAT_HTML);
        $parent->args[0]->value = new \PhpParser\Node\Scalar\String_(self::ADD_HTML_PART);
        $parent->args[1]->value = $addHtmlPart ? $this->nodeFactory->createTrue() : $this->nodeFactory->createFalse();
    }
    private function refactorArrayItemOption(\PhpParser\Node\Expr\ArrayItem $parent, \PhpParser\Node\Expr\ClassConstFetch $node) : void
    {
        if (null === $parent->key || !$this->valueResolver->isValue($parent->key, self::FORMAT)) {
            return;
        }
        $addHtmlPart = $this->isName($node->name, self::FORMAT_HTML);
        $parent->key = new \PhpParser\Node\Scalar\String_(self::ADD_HTML_PART);
        $parent->value = $addHtmlPart ? $this->nodeFactory->createTrue() : $this->nodeFactory->createFalse();
    }
    private function refactorOptionAssignment(\PhpParser\Node\Expr\Assign $parent, \PhpParser\Node\Expr\ClassConstFetch $node) : void
    {
        if (!$parent->var instanceof \PhpParser\Node\Expr\ArrayDimFetch) {
            return;
        }
        if (!$this->isName($parent->var->var, 'options')) {
            return;
        }
        if (null === $parent->var->dim || !$this->valueResolver->isValue($parent->var->dim, self::FORMAT)) {
            return;
        }
        $addHtmlPart = $this->isName($node->name, self::FORMAT_HTML);
        $parent->var->dim = new \PhpParser\Node\Scalar\String_(self::ADD_HTML_PART);
        $parent->expr = $addHtmlPart ? $this->nodeFactory->createTrue() : $this->nodeFactory->createFalse();
    }
    private function refactorCondition(\PhpParser\Node\Expr\BinaryOp\Identical $parent, \PhpParser\Node\Expr\ClassConstFetch $node) : void
    {
        if (!$parent->left instanceof \PhpParser\Node\Expr\ArrayDimFetch) {
            return;
        }
        if (!$this->isName($parent->left->var, 'options')) {
            return;
        }
        if (null === $parent->left->dim || !$this->valueResolver->isValue($parent->left->dim, self::FORMAT)) {
            return;
        }
        $addHtmlPart = $this->isName($node->name, self::FORMAT_HTML);
        $parent->left->dim = new \PhpParser\Node\Scalar\String_(self::ADD_HTML_PART);
        $parent->right = $addHtmlPart ? $this->nodeFactory->createTrue() : $this->nodeFactory->createFalse();
    }
}
