<?php

declare(strict_types=1);

namespace Rector\Nette\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\BinaryOp\Coalesce;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Param;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\NodeTraverser;
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
final class TranslateClassMethodToVariadicsRector extends AbstractRector
{
    /**
     * @var string
     */
    private const PARAMETERS = 'parameters';

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Change translate() method call 2nd arg to variadic',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
use Nette\Localization\ITranslator;

final class SomeClass implements ITranslator
{
    public function translate($message, $count = null)
    {
        return [$message, $count];
    }
}
CODE_SAMPLE
,
                    <<<'CODE_SAMPLE'
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
                ),

            ]);
    }

    /**
     * @return string[]
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
        if (! $this->nodeTypeResolver->isMethodStaticCallOrClassMethodObjectType(
            $node,
            'Nette\Localization\ITranslator'
        )) {
            return null;
        }

        if (! $this->isName($node->name, 'translate')) {
            return null;
        }

        if (! isset($node->params[1])) {
            return null;
        }

        $secondParam = $node->params[1];
        if (! $secondParam->var instanceof Variable) {
            return null;
        }

        if ($secondParam->variadic) {
            return null;
        }

        $this->replaceSecondParamInClassMethodBody($node, $secondParam);

        $secondParam->default = null;
        $secondParam->variadic = true;
        $secondParam->var->name = self::PARAMETERS;

        return $node;
    }

    private function replaceSecondParamInClassMethodBody(ClassMethod $classMethod, Param $param): void
    {
        $paramName = $this->getName($param->var);
        if ($paramName === null) {
            return;
        }

        $this->traverseNodesWithCallable((array) $classMethod->stmts, function (Node $node) use ($paramName): ?int {
            if (! $node instanceof Variable) {
                return null;
            }

            if (! $this->isName($node, $paramName)) {
                return null;
            }

            // instantiate
            $assign = $this->createCoalesceAssign($node);

            $currentStmt = $node->getAttribute(AttributeKey::CURRENT_STATEMENT);
            $positionNode = $currentStmt ?? $node;
            $this->addNodeBeforeNode($assign, $positionNode);

            return NodeTraverser::STOP_TRAVERSAL;
        });
    }

    private function createCoalesceAssign(Variable $variable): Assign
    {
        $arrayDimFetch = new ArrayDimFetch(new Variable(self::PARAMETERS), new LNumber(0));
        $coalesce = new Coalesce($arrayDimFetch, $this->nodeFactory->createNull());

        return new Assign(new Variable($variable->name), $coalesce);
    }
}
