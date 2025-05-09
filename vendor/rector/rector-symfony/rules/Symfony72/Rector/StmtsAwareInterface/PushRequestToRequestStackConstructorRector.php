<?php

declare (strict_types=1);
namespace Rector\Symfony\Symfony72\Rector\StmtsAwareInterface;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\ArrayItem;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Stmt\Expression;
use Rector\Contract\PhpParser\Node\StmtsAwareInterface;
use Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer;
use Rector\Rector\AbstractRector;
use Rector\Symfony\Enum\SymfonyClass;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see https://symfony.com/blog/new-in-symfony-7-2-misc-improvements-part-2#simpler-requeststack-unit-testing
 *
 * @see \Rector\Symfony\Tests\Symfony72\Rector\StmtsAwareInterface\PushRequestToRequestStackConstructorRector\PushRequestToRequestStackConstructorRectorTest
 */
final class PushRequestToRequestStackConstructorRector extends AbstractRector
{
    /**
     * @readonly
     */
    private TestsNodeAnalyzer $testsNodeAnalyzer;
    public function __construct(TestsNodeAnalyzer $testsNodeAnalyzer)
    {
        $this->testsNodeAnalyzer = $testsNodeAnalyzer;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Move push(request) to "Symfony\\Component\\HttpFoundation\\RequestStack" constructor', [new CodeSample(<<<'CODE_SAMPLE'
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use PHPUnit\Framework\TestCase;

final class SomeClass extends TestCase
{
    public function run()
    {
        $requestStack = new RequestStack();
        $request = new Request();
        $requestStack->push($request);
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use PHPUnit\Framework\TestCase;

class SomeClass extends TestCase
{
    public function run()
    {
        $request = new Request();
        $requestStack = new RequestStack([$request]);
    }
}
CODE_SAMPLE
)]);
    }
    public function getNodeTypes() : array
    {
        return [StmtsAwareInterface::class];
    }
    /**
     * @param StmtsAwareInterface $node
     */
    public function refactor(Node $node) : ?StmtsAwareInterface
    {
        if (!$this->testsNodeAnalyzer->isInTestClass($node)) {
            return null;
        }
        $requestStack = null;
        $hasChanged = \false;
        foreach ($node->stmts as $key => $stmt) {
            if (!$stmt instanceof Expression) {
                continue;
            }
            if (!$requestStack instanceof New_) {
                $requestStack = $this->matchRequestStackAssignExpr($stmt);
            } elseif ($stmt->expr instanceof MethodCall) {
                $pushMethodCall = $stmt->expr;
                if ($this->isName($pushMethodCall->name, 'push')) {
                    // possibly request stack push
                    $array = new Array_([new ArrayItem($pushMethodCall->getArgs()[0]->value)]);
                    $requestStack->args[] = new Arg($array);
                    $hasChanged = \true;
                    unset($node->stmts[$key]);
                }
            }
        }
        if ($hasChanged) {
            return $node;
        }
        return null;
    }
    private function matchRequestStackAssignExpr(Expression $expression) : ?New_
    {
        // 1. find new RequestStack assign
        if (!$expression->expr instanceof Assign) {
            return null;
        }
        $assign = $expression->expr;
        if (!$assign->expr instanceof New_) {
            return null;
        }
        $new = $assign->expr;
        if (!$this->isName($new->class, SymfonyClass::REQUEST_STACK)) {
            return null;
        }
        // skip if already some args are filled
        if ($new->getArgs() !== []) {
            return null;
        }
        return $new;
    }
}
