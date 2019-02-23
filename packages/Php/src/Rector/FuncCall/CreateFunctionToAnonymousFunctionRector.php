<?php declare(strict_types=1);

namespace Rector\Php\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\BinaryOp\Concat;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\ClosureUse;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name;
use PhpParser\Node\Param;
use PhpParser\Node\Scalar\Encapsed;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Expression;
use Rector\NodeTypeResolver\Node\Attribute;
use Rector\PhpParser\Node\BetterNodeFinder;
use Rector\PhpParser\Parser\InlineCodeParser;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * @see https://stackoverflow.com/q/48161526/1348344
 * @see http://php.net/manual/en/migration72.deprecated.php#migration72.deprecated.create_function-function
 */
final class CreateFunctionToAnonymousFunctionRector extends AbstractRector
{
    /**
     * @var BetterNodeFinder
     */
    private $betterNodeFinder;

    /**
     * @var InlineCodeParser
     */
    private $inlineCodeParser;

    public function __construct(BetterNodeFinder $betterNodeFinder, InlineCodeParser $inlineCodeParser)
    {
        $this->betterNodeFinder = $betterNodeFinder;
        $this->inlineCodeParser = $inlineCodeParser;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Use anonymous functions instead of deprecated create_function()', [
            new CodeSample(
                <<<'CODE_SAMPLE'
class ClassWithCreateFunction
{
    public function run()
    {
        $callable = create_function('$matches', "return '$delimiter' . strtolower(\$matches[1]);");
    }
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
class ClassWithCreateFunction
{
    public function run()
    {
        $callable = function($matches) use ($delimiter) {
            return $delimiter . strtolower($matches[1]);
        };
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
        return [FuncCall::class];
    }

    /**
     * @param FuncCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->isName($node, 'create_function')) {
            return null;
        }

        /** @var Variable[] $parameters */
        $parameters = $this->parseStringToParameters($node->args[0]->value);
        $body = $this->parseStringToBody($node->args[1]->value);
        $useVariables = $this->resolveUseVariables($body, $parameters);

        $anonymousFunctionNode = new Closure();

        foreach ($parameters as $parameter) {
            /** @var Variable $parameter */
            $anonymousFunctionNode->params[] = new Param($parameter);
        }

        if ($body !== []) {
            $anonymousFunctionNode->stmts = $body;
        }

        foreach ($useVariables as $useVariable) {
            $anonymousFunctionNode->uses[] = new ClosureUse($useVariable);
        }

        return $anonymousFunctionNode;
    }

    /**
     * @return Param[]
     */
    private function parseStringToParameters(Expr $expr): array
    {
        $content = $this->inlineCodeParser->stringify($expr);
        $content = '<?php $value = function(' . $content . ') {};';

        $nodes = $this->inlineCodeParser->parse($content);

        return $nodes[0]->expr->expr->params;
    }

    /**
     * @param String_|Expr $node
     * @return Stmt[]
     */
    private function parseStringToBody(Node $node): array
    {
        if (! $node instanceof String_ && ! $node instanceof Encapsed && ! $node instanceof Concat) {
            // special case of code elsewhere
            return [$this->createEval($node)];
        }

        $node = $this->inlineCodeParser->stringify($node);

        return $this->inlineCodeParser->parse($node);
    }

    /**
     * @param Node[] $nodes
     * @param Variable[] $paramNodes
     * @return Variable[]
     */
    private function resolveUseVariables(array $nodes, array $paramNodes): array
    {
        $paramNames = [];
        foreach ($paramNodes as $paramNode) {
            $paramNames[] = $this->getName($paramNode);
        }

        $variableNodes = $this->betterNodeFinder->findInstanceOf($nodes, Variable::class);

        $filteredVariables = [];
        $alreadyAssignedVariables = [];
        foreach ($variableNodes as $variableNode) {
            // "$this" is allowed
            if ($this->isName($variableNode, 'this')) {
                continue;
            }

            $variableName = $this->getName($variableNode);
            if ($variableName === null) {
                continue;
            }

            if (in_array($variableName, $paramNames, true)) {
                continue;
            }

            if ($variableNode->getAttribute(Attribute::PARENT_NODE) instanceof Assign) {
                $alreadyAssignedVariables[] = $variableName;
            }

            if ($this->isNames($variableNode, $alreadyAssignedVariables)) {
                continue;
            }

            $filteredVariables[$variableName] = $variableNode;
        }

        return $filteredVariables;
    }

    private function createEval(Expr $expr): Expression
    {
        $evalFuncCall = new FuncCall(new Name('eval'), [new Arg($expr)]);

        return new Expression($evalFuncCall);
    }
}
