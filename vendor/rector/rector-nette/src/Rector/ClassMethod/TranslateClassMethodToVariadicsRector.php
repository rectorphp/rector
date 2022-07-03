<?php

declare (strict_types=1);
namespace Rector\Nette\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\BinaryOp\Coalesce;
use PhpParser\Node\Expr\Error;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Param;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\NodeTraverser;
use PHPStan\Reflection\ClassReflection;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\Reflection\ReflectionResolver;
use Rector\PostRector\Collector\NodesToAddCollector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://github.com/nette/utils/pull/178
 * @changelog https://github.com/contributte/translation/commit/d374c4c05b57dff1e5b327bb9bf98c392769806c
 *
 * @see \Rector\Nette\Tests\Rector\ClassMethod\TranslateClassMethodToVariadicsRector\TranslateClassMethodToVariadicsRectorTest
 *
 * @note must be run before "composer update nette/utils:^3.0", because param contract break causes fatal error
 */
final class TranslateClassMethodToVariadicsRector extends AbstractRector
{
    /**
     * @var string
     */
    private const PARAMETERS = 'parameters';
    /**
     * @readonly
     * @var \Rector\Core\Reflection\ReflectionResolver
     */
    private $reflectionResolver;
    /**
     * @readonly
     * @var \Rector\PostRector\Collector\NodesToAddCollector
     */
    private $nodesToAddCollector;
    public function __construct(ReflectionResolver $reflectionResolver, NodesToAddCollector $nodesToAddCollector)
    {
        $this->reflectionResolver = $reflectionResolver;
        $this->nodesToAddCollector = $nodesToAddCollector;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Change translate() method call 2nd arg to variadic', [new CodeSample(<<<'CODE_SAMPLE'
use Nette\Localization\ITranslator;

final class SomeClass implements ITranslator
{
    public function translate($message, $count = null)
    {
        return [$message, $count];
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Nette\Localization\ITranslator;

final class SomeClass implements ITranslator
{
    public function translate($message, ... $parameters)
    {
        $count = $parameters[0] ?? null;
        return [$message, $count];
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
        $classReflection = $this->reflectionResolver->resolveClassReflection($node);
        if (!$classReflection instanceof ClassReflection) {
            return null;
        }
        if (!$classReflection->isSubclassOf('Nette\\Localization\\ITranslator')) {
            return null;
        }
        if (!$this->isName($node->name, 'translate')) {
            return null;
        }
        if (!isset($node->params[1])) {
            return null;
        }
        $secondParam = $node->params[1];
        if (!$secondParam->var instanceof Variable) {
            return null;
        }
        if ($secondParam->variadic) {
            return null;
        }
        $this->replaceSecondParamInClassMethodBody($node, $secondParam);
        $secondParam->default = null;
        $secondParam->variadic = \true;
        if ($secondParam->var instanceof Error) {
            throw new ShouldNotHappenException();
        }
        $secondParam->var->name = self::PARAMETERS;
        return $node;
    }
    private function replaceSecondParamInClassMethodBody(ClassMethod $classMethod, Param $param) : void
    {
        $paramName = $this->getName($param->var);
        if ($paramName === null) {
            return;
        }
        $this->traverseNodesWithCallable((array) $classMethod->stmts, function (Node $node) use($paramName) : ?int {
            if (!$node instanceof Variable) {
                return null;
            }
            if (!$this->isName($node, $paramName)) {
                return null;
            }
            // instantiate
            $assign = $this->createCoalesceAssign($node);
            $this->nodesToAddCollector->addNodeBeforeNode($assign, $node);
            return NodeTraverser::STOP_TRAVERSAL;
        });
    }
    private function createCoalesceAssign(Variable $variable) : Assign
    {
        $arrayDimFetch = new ArrayDimFetch(new Variable(self::PARAMETERS), new LNumber(0));
        $coalesce = new Coalesce($arrayDimFetch, $this->nodeFactory->createNull());
        return new Assign(new Variable($variable->name), $coalesce);
    }
}
