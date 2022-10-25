<?php

declare (strict_types=1);
namespace Rector\Symfony\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Type\ObjectType;
use Rector\Core\Rector\AbstractRector;
use Rector\Symfony\TypeAnalyzer\ControllerAnalyzer;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Symfony\Tests\Rector\ClassMethod\RemoveUnusedRequestParamRector\RemoveUnusedRequestParamRectorTest
 */
final class RemoveUnusedRequestParamRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\Symfony\TypeAnalyzer\ControllerAnalyzer
     */
    private $controllerAnalyzer;
    public function __construct(ControllerAnalyzer $controllerAnalyzer)
    {
        $this->controllerAnalyzer = $controllerAnalyzer;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Remove unused $request parameter from controller action', [new CodeSample(<<<'CODE_SAMPLE'
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

final class SomeController extends Controller
{
    public function run(Request $request, int $id)
    {
        echo $id;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

final class SomeController extends Controller
{
    public function run(int $id)
    {
        echo $id;
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
        if (!$node->isPublic()) {
            return null;
        }
        if (!$this->controllerAnalyzer->isInsideController($node)) {
            return null;
        }
        if ($node->getParams() === []) {
            return null;
        }
        // skip empty method
        if ($node->stmts === null) {
            return null;
        }
        foreach ($node->getParams() as $paramPosition => $param) {
            if (!$param->type instanceof Node) {
                continue;
            }
            if (!$this->isObjectType($param->type, new ObjectType('Symfony\\Component\\HttpFoundation\\Request'))) {
                continue;
            }
            /** @var string $requestParamName */
            $requestParamName = $this->getName($param);
            // we have request param here
            $requestVariable = $this->betterNodeFinder->findVariableOfName($node->stmts, $requestParamName);
            // is variable used?
            if ($requestVariable instanceof Variable) {
                return null;
            }
            unset($node->params[$paramPosition]);
            return $node;
        }
        return null;
    }
}
