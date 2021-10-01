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
use PHPStan\Type\ObjectType;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see https://github.com/nette/utils/pull/178
 * @see https://github.com/contributte/translation/commit/d374c4c05b57dff1e5b327bb9bf98c392769806c
 *
 * @see \Rector\Nette\Tests\Rector\ClassMethod\TranslateClassMethodToVariadicsRector\TranslateClassMethodToVariadicsRectorTest
 * @note must be run before "composer update nette/utils:^3.0", because param contract break causes fatal error
 */
final class TranslateClassMethodToVariadicsRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @var string
     */
    private const PARAMETERS = 'parameters';
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Change translate() method call 2nd arg to variadic', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
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
        return [\PhpParser\Node\Stmt\ClassMethod::class];
    }
    /**
     * @param ClassMethod $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if (!$this->nodeTypeResolver->isMethodStaticCallOrClassMethodObjectType($node, new \PHPStan\Type\ObjectType('Nette\\Localization\\ITranslator'))) {
            return null;
        }
        if (!$this->isName($node->name, 'translate')) {
            return null;
        }
        if (!isset($node->params[1])) {
            return null;
        }
        $secondParam = $node->params[1];
        if (!$secondParam->var instanceof \PhpParser\Node\Expr\Variable) {
            return null;
        }
        if ($secondParam->variadic) {
            return null;
        }
        $this->replaceSecondParamInClassMethodBody($node, $secondParam);
        $secondParam->default = null;
        $secondParam->variadic = \true;
        if ($secondParam->var instanceof \PhpParser\Node\Expr\Error) {
            throw new \Rector\Core\Exception\ShouldNotHappenException();
        }
        $secondParam->var->name = self::PARAMETERS;
        return $node;
    }
    private function replaceSecondParamInClassMethodBody(\PhpParser\Node\Stmt\ClassMethod $classMethod, \PhpParser\Node\Param $param) : void
    {
        $paramName = $this->getName($param->var);
        if ($paramName === null) {
            return;
        }
        $this->traverseNodesWithCallable((array) $classMethod->stmts, function (\PhpParser\Node $node) use($paramName) : ?int {
            if (!$node instanceof \PhpParser\Node\Expr\Variable) {
                return null;
            }
            if (!$this->isName($node, $paramName)) {
                return null;
            }
            // instantiate
            $assign = $this->createCoalesceAssign($node);
            $currentStmt = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::CURRENT_STATEMENT);
            $positionNode = $currentStmt ?? $node;
            $this->nodesToAddCollector->addNodeBeforeNode($assign, $positionNode);
            return \PhpParser\NodeTraverser::STOP_TRAVERSAL;
        });
    }
    private function createCoalesceAssign(\PhpParser\Node\Expr\Variable $variable) : \PhpParser\Node\Expr\Assign
    {
        $arrayDimFetch = new \PhpParser\Node\Expr\ArrayDimFetch(new \PhpParser\Node\Expr\Variable(self::PARAMETERS), new \PhpParser\Node\Scalar\LNumber(0));
        $coalesce = new \PhpParser\Node\Expr\BinaryOp\Coalesce($arrayDimFetch, $this->nodeFactory->createNull());
        return new \PhpParser\Node\Expr\Assign(new \PhpParser\Node\Expr\Variable($variable->name), $coalesce);
    }
}
