<?php

declare (strict_types=1);
namespace Rector\Symfony\CodeQuality\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Return_;
use Rector\Rector\AbstractRector;
use Rector\Symfony\Bridge\NodeAnalyzer\ControllerMethodAnalyzer;
use Rector\Symfony\CodeQuality\Enum\ResponseClass;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Symfony\Tests\CodeQuality\Rector\ClassMethod\ReturnDirectJsonResponseRector\ReturnDirectJsonResponseRectorTest
 */
final class ReturnDirectJsonResponseRector extends AbstractRector
{
    /**
     * @readonly
     */
    private ControllerMethodAnalyzer $controllerMethodAnalyzer;
    public function __construct(ControllerMethodAnalyzer $controllerMethodAnalyzer)
    {
        $this->controllerMethodAnalyzer = $controllerMethodAnalyzer;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Instead of creating JsonResponse, setting items and data, return it directly at once to improve readability', [new CodeSample(<<<'CODE_SAMPLE'
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class SomeController extends AbstractController
{
    public function index()
    {
        $response = new JsonResponse();
        $response->setData(['key' => 'value']);

        return $response;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class SomeController extends AbstractController
{
    public function index()
    {
        return new JsonResponse(['key' => 'value']);
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [ClassMethod::class];
    }
    /**
     * @param ClassMethod $node
     */
    public function refactor(Node $node): ?Node
    {
        if (!$this->controllerMethodAnalyzer->isAction($node)) {
            return null;
        }
        $jsonResponseVariable = null;
        $assignStmtPosition = null;
        $setDataExpr = null;
        $setDataPosition = null;
        foreach ((array) $node->stmts as $key => $stmt) {
            if ($assignStmtPosition === null) {
                $jsonResponseVariable = $this->matchNewJsonResponseAssignVariable($stmt);
                if ($jsonResponseVariable instanceof Variable) {
                    $assignStmtPosition = $key;
                    continue;
                }
            }
            if ($setDataPosition === null && $jsonResponseVariable instanceof Variable) {
                $setDataExpr = $this->matchSetDataMethodCallExpr($stmt, $jsonResponseVariable);
                if ($setDataExpr instanceof Expr) {
                    $setDataPosition = $key;
                    continue;
                }
            }
            if (!$stmt instanceof Return_) {
                continue;
            }
            // missing important data
            if (!$jsonResponseVariable instanceof Variable || !$setDataExpr instanceof Expr) {
                continue;
            }
            if (!$this->nodeComparator->areNodesEqual($stmt->expr, $jsonResponseVariable)) {
                continue;
            }
            $jsonResponseNew = new New_(new FullyQualified(ResponseClass::JSON), [new Arg($setDataExpr)]);
            $stmt->expr = $jsonResponseNew;
            // remove previous setData and assignment statements
            unset($node->stmts[$assignStmtPosition], $node->stmts[$setDataPosition]);
            // reindex statements
            $node->stmts = array_values($node->stmts);
            return $node;
        }
        return null;
    }
    private function matchNewJsonResponseAssignVariable(Stmt $stmt): ?Variable
    {
        if (!$stmt instanceof Expression) {
            return null;
        }
        if (!$stmt->expr instanceof Assign) {
            return null;
        }
        $assign = $stmt->expr;
        if (!$assign->expr instanceof New_) {
            return null;
        }
        if (!$this->isName($assign->expr->class, ResponseClass::JSON)) {
            return null;
        }
        if (!$assign->var instanceof Variable) {
            return null;
        }
        return $assign->var;
    }
    private function matchSetDataMethodCallExpr(Stmt $stmt, Variable $jsonResponseVariable): ?Expr
    {
        if (!$stmt instanceof Expression) {
            return null;
        }
        if (!$stmt->expr instanceof MethodCall) {
            return null;
        }
        $methodCall = $stmt->expr;
        if (!$this->isName($methodCall->name, 'setData')) {
            return null;
        }
        if (!$this->nodeComparator->areNodesEqual($methodCall->var, $jsonResponseVariable)) {
            return null;
        }
        if ($methodCall->isFirstClassCallable()) {
            return null;
        }
        if ($methodCall->getArgs() === []) {
            return null;
        }
        $soleArg = $methodCall->getArgs()[0];
        return $soleArg->value;
    }
}
