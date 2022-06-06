<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\Symfony\Rector\ClassMethod;

use RectorPrefix20220606\PhpParser\Node;
use RectorPrefix20220606\PhpParser\Node\Expr;
use RectorPrefix20220606\PhpParser\Node\Expr\BinaryOp\Coalesce;
use RectorPrefix20220606\PhpParser\Node\Expr\Cast\Int_;
use RectorPrefix20220606\PhpParser\Node\Expr\Ternary;
use RectorPrefix20220606\PhpParser\Node\FunctionLike;
use RectorPrefix20220606\PhpParser\Node\Identifier;
use RectorPrefix20220606\PhpParser\Node\Scalar\LNumber;
use RectorPrefix20220606\PhpParser\Node\Stmt\Class_;
use RectorPrefix20220606\PhpParser\Node\Stmt\ClassMethod;
use RectorPrefix20220606\PhpParser\Node\Stmt\Return_;
use RectorPrefix20220606\PhpParser\NodeTraverser;
use RectorPrefix20220606\PHPStan\Type\IntegerType;
use RectorPrefix20220606\PHPStan\Type\ObjectType;
use RectorPrefix20220606\Rector\Core\Rector\AbstractRector;
use RectorPrefix20220606\Rector\NodeTypeResolver\Node\AttributeKey;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see https://github.com/symfony/symfony/pull/33775/files
 * @see \Rector\Symfony\Tests\Rector\ClassMethod\ConsoleExecuteReturnIntRector\ConsoleExecuteReturnIntRectorTest
 */
final class ConsoleExecuteReturnIntRector extends AbstractRector
{
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
        return [ClassMethod::class];
    }
    /**
     * @param ClassMethod $node
     */
    public function refactor(Node $node) : ?Node
    {
        if (!$this->isName($node, 'execute')) {
            return null;
        }
        $class = $this->betterNodeFinder->findParentType($node, Class_::class);
        if (!$class instanceof Class_) {
            return null;
        }
        if (!$this->isObjectType($class, new ObjectType('Symfony\\Component\\Console\\Command\\Command'))) {
            return null;
        }
        $this->refactorReturnTypeDeclaration($node);
        $this->addReturn0ToMethod($node);
        return $node;
    }
    private function refactorReturnTypeDeclaration(ClassMethod $classMethod) : void
    {
        // already set
        if ($classMethod->returnType !== null && $this->isName($classMethod->returnType, 'int')) {
            return;
        }
        $classMethod->returnType = new Identifier('int');
    }
    private function addReturn0ToMethod(ClassMethod $classMethod) : void
    {
        $hasReturn = \false;
        $this->traverseNodesWithCallable((array) $classMethod->getStmts(), function (Node $node) use($classMethod, &$hasReturn) : ?int {
            if ($node instanceof FunctionLike) {
                return NodeTraverser::DONT_TRAVERSE_CHILDREN;
            }
            $parentNode = $node->getAttribute(AttributeKey::PARENT_NODE);
            if ($parentNode instanceof Node && $this->isReturnWithExprIntEquals($parentNode, $node)) {
                $hasReturn = \true;
                return null;
            }
            if (!$node instanceof Return_) {
                return null;
            }
            if ($node->expr instanceof Int_) {
                return null;
            }
            if ($node->expr instanceof Ternary && $this->isIntegerTernaryIfElse($node->expr)) {
                $hasReturn = \true;
                return null;
            }
            // is there return without nesting?
            if ($this->nodeComparator->areNodesEqual($parentNode, $classMethod)) {
                $hasReturn = \true;
            }
            $this->setReturnTo0InsteadOfNull($node);
            return null;
        });
        $this->processReturn0ToMethod($hasReturn, $classMethod);
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
        $classMethod->stmts[] = new Return_(new LNumber(0));
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
        if ($return->expr === null) {
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
