<?php declare(strict_types=1);

namespace Rector\Php\Rector\FuncCall;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp\Concat;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\ClosureUse;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name;
use PhpParser\Node\Param;
use PhpParser\Node\Scalar\Encapsed;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Parser;
use Rector\Exception\ShouldNotHappenException;
use Rector\PhpParser\Node\BetterNodeFinder;
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
     * @var Parser
     */
    private $parser;

    /**
     * @var BetterNodeFinder
     */
    private $betterNodeFinder;

    public function __construct(Parser $parser, BetterNodeFinder $betterNodeFinder)
    {
        $this->parser = $parser;
        $this->betterNodeFinder = $betterNodeFinder;
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
        $content = $this->stringify($expr);

        $content = '<?php $value = function(' . $content . ') {};';

        $nodes = $this->parser->parse($content);

        return $nodes[0]->expr->expr->params;
    }

    /**
     * @param String_|Expr $content
     * @return Stmt[]
     */
    private function parseStringToBody(Node $content): array
    {
        if (! $content instanceof String_ && ! $content instanceof Encapsed && ! $content instanceof Concat) {
            // special case of code elsewhere
            return [$this->createEval($content)];
        }

        $content = $this->stringify($content);
        $content = Strings::endsWith($content, ';') ? $content : $content . ';';

        return (array) $this->parser->parse('<?php ' . $content);
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
        foreach ($variableNodes as $variableNode) {
            // "$this" is allowed
            if ($this->isName($variableNode, 'this')) {
                continue;
            }

            $variableName = $this->getName($variableNode);
            if (in_array($variableName, $paramNames, true)) {
                continue;
            }

            $filteredVariables[$variableName] = $variableNode;
        }

        return $filteredVariables;
    }

    /**
     * @param string|Node $content
     */
    private function stringify($content): string
    {
        if (is_string($content)) {
            return $content;
        }

        if ($content instanceof String_) {
            return $content->value;
        }

        if ($content instanceof Encapsed) {
            // remove "
            $content = trim($this->print($content), '""');
            // use \$ → $
            $content = Strings::replace($content, '#\\\\\$#', '$');
            // use \'{$...}\' → $...
            return Strings::replace($content, '#\'{(\$.*?)}\'#', '$1');
        }

        if ($content instanceof Concat) {
            return $this->stringify($content->left) . $this->stringify($content->right);
        }

        if ($content instanceof Variable || $content instanceof PropertyFetch) {
            return $this->print($content);
        }

        throw new ShouldNotHappenException(get_class($content) . ' ' . __METHOD__);
    }

    private function createEval(Expr $node): Expression
    {
        $evalFuncCall = new FuncCall(new Name('eval'), [new Arg($node)]);

        return new Expression($evalFuncCall);
    }
}
