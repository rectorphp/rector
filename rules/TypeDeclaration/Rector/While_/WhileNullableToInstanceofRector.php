<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\Rector\While_;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp\NotIdentical;
use PhpParser\Node\Expr\Instanceof_;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\While_;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use Rector\Core\Rector\AbstractRector;
use Rector\TypeDeclaration\TypeAnalyzer\NullableTypeAnalyzer;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\TypeDeclaration\Rector\While_\WhileNullableToInstanceofRector\WhileNullableToInstanceofRectorTest
 */
final class WhileNullableToInstanceofRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\TypeDeclaration\TypeAnalyzer\NullableTypeAnalyzer
     */
    private $nullableTypeAnalyzer;
    public function __construct(NullableTypeAnalyzer $nullableTypeAnalyzer)
    {
        $this->nullableTypeAnalyzer = $nullableTypeAnalyzer;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Change while null compare to strict instanceof check', [new CodeSample(<<<'CODE_SAMPLE'
final class SomeClass
{
    public function run(?SomeClass $someClass)
    {
        while ($someClass !== null) {
            // do something
        }
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
final class SomeClass
{
    public function run(?SomeClass $someClass)
    {
        while ($someClass instanceof SomeClass) {
            // do something
        }
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
        return [While_::class];
    }
    /**
     * @param While_ $node
     */
    public function refactor(Node $node) : ?Node
    {
        if ($node->cond instanceof NotIdentical) {
            return $this->refactorNotIdentical($node, $node->cond);
        }
        $condNullableObjectType = $this->nullableTypeAnalyzer->resolveNullableObjectType($node->cond);
        if (!$condNullableObjectType instanceof Type) {
            return null;
        }
        $node->cond = $this->createInstanceof($node->cond, $condNullableObjectType);
        return $node;
    }
    private function createInstanceof(Expr $expr, ObjectType $objectType) : Instanceof_
    {
        $fullyQualified = new FullyQualified($objectType->getClassName());
        return new Instanceof_($expr, $fullyQualified);
    }
    private function refactorNotIdentical(While_ $while, NotIdentical $notIdentical) : ?\PhpParser\Node\Stmt\While_
    {
        if (!$this->valueResolver->isNull($notIdentical->right)) {
            return null;
        }
        $condNullableObjectType = $this->nullableTypeAnalyzer->resolveNullableObjectType($notIdentical->left);
        if (!$condNullableObjectType instanceof ObjectType) {
            return null;
        }
        $while->cond = $this->createInstanceof($notIdentical->left, $condNullableObjectType);
        return $while;
    }
}
