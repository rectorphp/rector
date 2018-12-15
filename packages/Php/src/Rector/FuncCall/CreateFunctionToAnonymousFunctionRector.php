<?php declare(strict_types=1);

namespace Rector\Php\Rector\FuncCall;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\ClosureUse;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Variable;
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
        $parameters = $this->parseStringToNodes($node->args[0]->value);
        $body = $this->parseStringToNodes($node->args[1]->value);

        $useVariables = $this->resolveUseVariables($body, $parameters);

        $anonymousFunctionNode = new Closure();
        foreach ($parameters as $parameter) {
            /** @var Variable $parameter */
            $anonymousFunctionNode->params[] = new Param($parameter);
        }

        if ($body) {
            $anonymousFunctionNode->stmts = $body;
        }

        foreach ($useVariables as $useVariable) {
            $anonymousFunctionNode->uses[] = new ClosureUse($useVariable);
        }

        return $anonymousFunctionNode;
    }

    /**
     * @param string|String_|Node $content
     * @return Node[]|Variable[]|Stmt[]
     */
    private function parseStringToNodes($content): array
    {
        if ($content instanceof String_) {
            $content = $content->value;
        }

        if ($content instanceof Encapsed) {
            // remove "
            $content = trim($this->print($content), '""');
            // use \$ → $
            $content = Strings::replace($content, '#\\\\\$#', '$');
            // use \'{$...}\' → $...
            $content = Strings::replace($content, '#\'{(\$.*?)}\'#', '$1');
        }

        if (! is_string($content)) {
            throw new ShouldNotHappenException();
        }

        $wrappedCode = '<?php ' . $content . (Strings::endsWith($content, ';') ? '' : ';');

        $nodes = $this->parser->parse($wrappedCode);
        if (count($nodes) === 1) {
            if ($nodes[0] instanceof Expression) {
                return [$nodes[0]->expr];
            }

            return [$nodes[0]];
        }

        // @todo implement
        throw new ShouldNotHappenException();
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

        /** @var Variable[] $variableNodes */
        $variableNodes = $this->betterNodeFinder->findInstanceOf($nodes, Variable::class);
        foreach ($variableNodes as $i => $variableNode) {
            if (! in_array($this->getName($variableNode), $paramNames, true)) {
                continue;
            }

            unset($variableNodes[$i]);
        }

        // re-index
        return array_values($variableNodes);
    }
}
