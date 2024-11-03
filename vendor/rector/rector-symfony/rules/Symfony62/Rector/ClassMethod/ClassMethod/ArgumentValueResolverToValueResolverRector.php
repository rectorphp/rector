<?php

declare (strict_types=1);
namespace Rector\Symfony\Symfony62\Rector\ClassMethod\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\BinaryOp\NotIdentical;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\Return_;
use Rector\Rector\AbstractRector;
use RectorPrefix202411\Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use RectorPrefix202411\Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Symfony\Tests\Symfony62\Rector\ClassMethod\ArgumentValueResolverToValueResolverRector\ArgumentValueResolverToValueResolverRectorTest
 */
final class ArgumentValueResolverToValueResolverRector extends AbstractRector
{
    public function getNodeTypes() : array
    {
        return [Class_::class];
    }
    /**
     * @param Class_ $node
     */
    public function refactor(Node $node) : ?Node
    {
        if (!$this->shouldRefactorClass($node)) {
            return null;
        }
        foreach ($node->getMethods() as $key => $classMethod) {
            if ($this->isName($classMethod->name, 'supports')) {
                [$isIdentical, $supportFirstArg, $supportSecondArg] = $this->extractSupportsArguments($node, $key, $classMethod);
            }
            if ($this->isName($classMethod->name, 'resolve') && isset($isIdentical) && isset($supportFirstArg) && isset($supportSecondArg)) {
                $this->processResolveMethod($classMethod, $isIdentical, $supportFirstArg, $supportSecondArg);
            }
        }
        return $node;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Replaces ArgumentValueResolverInterface by ValueResolverInterface', [new CodeSample(<<<'CODE_SAMPLE'
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;

final class EntityValueResolver implements ArgumentValueResolverInterface
{
    public function supports(Request $request, ArgumentMetadata $argument): bool
    {
    }

    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;

final class EntityValueResolver implements ValueResolverInterface
{
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
    }
}
CODE_SAMPLE
)]);
    }
    private function shouldRefactorClass(Class_ $class) : bool
    {
        // Check if the class implements ArgumentValueResolverInterface
        foreach ($class->implements as $key => $interface) {
            if ($interface->toString() === ArgumentValueResolverInterface::class) {
                $class->implements[$key] = new FullyQualified(ValueResolverInterface::class);
                return \true;
            }
        }
        // If it doesn't implement ArgumentValueResolverInterface, skip
        return \false;
    }
    /**
     * @return array{bool, Expr|null, Expr|null}
     */
    private function extractSupportsArguments(Class_ $class, int $key, ClassMethod $classMethod) : array
    {
        $isIdentical = \true;
        $supportFirstArg = $supportSecondArg = null;
        if ($classMethod->getStmts() === null) {
            return [$isIdentical, $supportFirstArg, $supportSecondArg];
        }
        foreach ($classMethod->getStmts() as $stmt) {
            if (!$stmt instanceof Return_) {
                continue;
            }
            $expression = $stmt->expr;
            if (!$expression instanceof BinaryOp) {
                continue;
            }
            if ($expression instanceof NotIdentical) {
                $isIdentical = \false;
            }
            $supportFirstArg = $expression->left;
            $supportSecondArg = $expression->right;
            unset($class->stmts[$key]);
            break;
            // We only need the first matching condition
        }
        return [$isIdentical, $supportFirstArg, $supportSecondArg];
    }
    private function processResolveMethod(ClassMethod $classMethod, bool $isIdentical, Expr $supportFirstArg, Expr $supportSecondArg) : void
    {
        $ifCondition = $isIdentical ? new NotIdentical($supportFirstArg, $supportSecondArg) : new Identical($supportFirstArg, $supportSecondArg);
        $classMethod->stmts = \array_merge([new If_($ifCondition, ['stmts' => [new Return_(new ConstFetch(new Name('[]')))]])], (array) $classMethod->stmts);
    }
}
