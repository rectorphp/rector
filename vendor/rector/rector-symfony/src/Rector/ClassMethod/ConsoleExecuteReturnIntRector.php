<?php

declare (strict_types=1);
namespace Rector\Symfony\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp\Coalesce;
use PhpParser\Node\Expr\Cast\Int_;
use PhpParser\Node\Expr\Ternary;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Identifier;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Return_;
use PhpParser\NodeTraverser;
use PHPStan\Type\IntegerType;
use PHPStan\Type\ObjectType;
use Rector\Core\NodeAnalyzer\TerminatedNodeAnalyzer;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see https://github.com/symfony/symfony/pull/33775/files
 * @see \Rector\Symfony\Tests\Rector\ClassMethod\ConsoleExecuteReturnIntRector\ConsoleExecuteReturnIntRectorTest
 */
final class ConsoleExecuteReturnIntRector extends AbstractRector
{
    /**
     * @var bool
     */
    private $hasChanged = \false;
    /**
     * @readonly
     * @var \Rector\Core\NodeAnalyzer\TerminatedNodeAnalyzer
     */
    private $terminatedNodeAnalyzer;
    public function __construct(TerminatedNodeAnalyzer $terminatedNodeAnalyzer)
    {
        $this->terminatedNodeAnalyzer = $terminatedNodeAnalyzer;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Returns int from Command::execute command', [new CodeSample(<<<'CODE_SAMPLE'
class SomeCommand extends Command
{
    public function execute(InputInterface $input, OutputInterface $output)
    {
        return null;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeCommand extends Command
{
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        return 0;
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
        return [Class_::class];
    }
    /**
     * @param Class_ $node
     */
    public function refactor(Node $node) : ?Node
    {
        if (!$this->isObjectType($node, new ObjectType('Symfony\\Component\\Console\\Command\\Command'))) {
            return null;
        }
        $executeClassMethod = $node->getMethod('execute');
        if (!$executeClassMethod instanceof ClassMethod) {
            return null;
        }
        $this->refactorReturnTypeDeclaration($executeClassMethod);
        $this->addReturn0ToMethod($executeClassMethod);
        if ($this->hasChanged) {
            return $node;
        }
        return null;
    }
    private function refactorReturnTypeDeclaration(ClassMethod $classMethod) : void
    {
        // already set
        if ($classMethod->returnType !== null && $this->isName($classMethod->returnType, 'int')) {
            return;
        }
        $classMethod->returnType = new Identifier('int');
        $this->hasChanged = \true;
    }
    private function addReturn0ToMethod(ClassMethod $classMethod) : void
    {
        $hasReturn = \false;
        $this->traverseNodesWithCallable((array) $classMethod->getStmts(), function (Node $node) use($classMethod, &$hasReturn) : ?int {
            if ($node instanceof FunctionLike) {
                return NodeTraverser::DONT_TRAVERSE_CURRENT_AND_CHILDREN;
            }
            // skip anonymous class
            if ($node instanceof Class_) {
                return NodeTraverser::DONT_TRAVERSE_CURRENT_AND_CHILDREN;
            }
            $parentNode = $node->getAttribute(AttributeKey::PARENT_NODE);
            if ($parentNode instanceof Node && $this->isReturnWithExprIntEquals($parentNode, $node)) {
                $hasReturn = \true;
                return null;
            }
            if (!$node instanceof Return_) {
                return null;
            }
            if ($this->isReturnIntegerType($node->expr)) {
                return null;
            }
            if ($node->expr instanceof Ternary && $this->isIntegerTernaryIfElse($node->expr)) {
                $hasReturn = \true;
                return null;
            }
            // is there return without nesting?
            if ($parentNode === $classMethod) {
                $hasReturn = \true;
            }
            $this->setReturnTo0InsteadOfNull($node);
            $this->hasChanged = \true;
            return null;
        });
        $this->processReturn0ToMethod($hasReturn, $classMethod);
    }
    private function isReturnIntegerType(?Expr $expr) : bool
    {
        if ($expr instanceof Expr) {
            $returnedType = $this->getType($expr);
            if ($returnedType instanceof IntegerType) {
                return \true;
            }
        }
        return \false;
    }
    private function isIntegerTernaryIfElse(Ternary $ternary) : bool
    {
        /** @var Expr|null $if */
        $if = $ternary->if;
        if (!$if instanceof Expr) {
            $if = $ternary->cond;
        }
        /** @var Expr $else */
        $else = $ternary->else;
        $ifType = $this->getType($if);
        $elseType = $this->getType($else);
        return $ifType instanceof IntegerType && $elseType instanceof IntegerType;
    }
    private function processReturn0ToMethod(bool $hasReturn, ClassMethod $classMethod) : void
    {
        if ($hasReturn) {
            return;
        }
        $stmts = (array) $classMethod->stmts;
        \end($stmts);
        $lastKey = \key($stmts);
        $return = new Return_(new LNumber(0));
        if ($lastKey !== null && (isset($classMethod->stmts[$lastKey]) && $this->terminatedNodeAnalyzer->isAlwaysTerminated($classMethod, $classMethod->stmts[$lastKey], $return))) {
            return;
        }
        $classMethod->stmts[] = $return;
    }
    private function isReturnWithExprIntEquals(Node $parentNode, Node $node) : bool
    {
        if (!$parentNode instanceof Return_) {
            return \false;
        }
        if (!$this->nodeComparator->areNodesEqual($parentNode->expr, $node)) {
            return \false;
        }
        return $node instanceof Int_;
    }
    private function setReturnTo0InsteadOfNull(Return_ $return) : void
    {
        if (!$return->expr instanceof Expr) {
            $return->expr = new LNumber(0);
            return;
        }
        if ($this->valueResolver->isNull($return->expr)) {
            $return->expr = new LNumber(0);
            return;
        }
        if ($return->expr instanceof Coalesce && $this->valueResolver->isNull($return->expr->right)) {
            $return->expr->right = new LNumber(0);
            return;
        }
        if ($return->expr instanceof Ternary) {
            $hasChanged = $this->isSuccessfulRefactorTernaryReturn($return->expr);
            if ($hasChanged) {
                return;
            }
        }
        $staticType = $this->getType($return->expr);
        if (!$staticType instanceof IntegerType) {
            $return->expr = new Int_($return->expr);
        }
    }
    private function isSuccessfulRefactorTernaryReturn(Ternary $ternary) : bool
    {
        $hasChanged = \false;
        if ($ternary->if instanceof Expr && $this->valueResolver->isNull($ternary->if)) {
            $ternary->if = new LNumber(0);
            $hasChanged = \true;
        }
        if ($this->valueResolver->isNull($ternary->else)) {
            $ternary->else = new LNumber(0);
            $hasChanged = \true;
        }
        return $hasChanged;
    }
}
