<?php

declare (strict_types=1);
namespace Rector\Symfony\Symfony72\Rector\StmtsAwareInterface;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\ArrayItem;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Expression;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PhpParser\Enum\NodeGroup;
use Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer;
use Rector\Rector\AbstractRector;
use Rector\Symfony\Enum\SymfonyClass;
use Rector\VersionBonding\Contract\ComposerPackageConstraintInterface;
use Rector\VersionBonding\ValueObject\ComposerPackageConstraint;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see https://symfony.com/blog/new-in-symfony-7-2-misc-improvements-part-2#simpler-requeststack-unit-testing
 *
 * @see \Rector\Symfony\Tests\Symfony72\Rector\StmtsAwareInterface\PushRequestToRequestStackConstructorRector\PushRequestToRequestStackConstructorRectorTest
 */
final class PushRequestToRequestStackConstructorRector extends AbstractRector implements ComposerPackageConstraintInterface
{
    /**
     * @readonly
     */
    private TestsNodeAnalyzer $testsNodeAnalyzer;
    public function __construct(TestsNodeAnalyzer $testsNodeAnalyzer)
    {
        $this->testsNodeAnalyzer = $testsNodeAnalyzer;
    }
    public function provideComposerPackageConstraint(): ComposerPackageConstraint
    {
        return new ComposerPackageConstraint('symfony/http-foundation', '>=7.2');
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Move push(request) to "Symfony\Component\HttpFoundation\RequestStack" constructor', [new CodeSample(<<<'CODE_SAMPLE'
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
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return NodeGroup::STMTS_AWARE;
    }
    /**
     * @param StmtsAware $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($node->stmts === null) {
            return null;
        }
        if (!$this->testsNodeAnalyzer->isInTestClass($node)) {
            return null;
        }
        // keep every "new RequestStack()" per variable name, to append the request pushed on that very variable
        // stored as [statement key, New_], so the assignment can be moved down to the push() position
        $requestStackNewsByVariableName = [];
        $hasChanged = \false;
        foreach ($node->stmts as $key => $stmt) {
            if (!$stmt instanceof Expression) {
                continue;
            }
            if ($stmt->expr instanceof Assign) {
                $assign = $stmt->expr;
                if (!$assign->var instanceof Variable || !is_string($assign->var->name)) {
                    continue;
                }
                $emptyRequestStackNew = $this->matchEmptyRequestStackNew($assign->expr);
                if ($emptyRequestStackNew instanceof New_) {
                    $requestStackNewsByVariableName[$assign->var->name] = [$key, $emptyRequestStackNew];
                }
                continue;
            }
            if (!$stmt->expr instanceof MethodCall) {
                continue;
            }
            $pushMethodCall = $stmt->expr;
            if (!$this->isName($pushMethodCall->name, 'push')) {
                continue;
            }
            if ($pushMethodCall->getArgs() === []) {
                continue;
            }
            if (!$pushMethodCall->var instanceof Variable || !is_string($pushMethodCall->var->name)) {
                continue;
            }
            $variableName = $pushMethodCall->var->name;
            $requestStackNewData = $requestStackNewsByVariableName[$variableName] ?? null;
            if ($requestStackNewData === null) {
                continue;
            }
            [$assignKey, $requestStackNew] = $requestStackNewData;
            $array = new Array_([new ArrayItem($pushMethodCall->getArgs()[0]->value)]);
            $requestStackNew->args[] = new Arg($array);
            $requestStackNew->setAttribute(AttributeKey::ORIGINAL_NODE, null);
            // move the "new RequestStack([...])" assignment down to the push() position, so any statements
            // between them - including the ones defining the pushed request - stay before it
            $node->stmts[$key] = $node->stmts[$assignKey];
            unset($node->stmts[$assignKey]);
            // the constructor takes a single array of requests, so no further push can be merged
            unset($requestStackNewsByVariableName[$variableName]);
            $hasChanged = \true;
        }
        if ($hasChanged) {
            return $node;
        }
        return null;
    }
    private function matchEmptyRequestStackNew(Expr $expr): ?New_
    {
        if (!$expr instanceof New_) {
            return null;
        }
        if (!$this->isName($expr->class, SymfonyClass::REQUEST_STACK)) {
            return null;
        }
        // skip if already some args are filled
        if ($expr->getArgs() !== []) {
            return null;
        }
        return $expr;
    }
}
