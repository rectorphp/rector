<?php declare(strict_types=1);

namespace Rector\Php\Rector\FuncCall;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name;
use PhpParser\Node\Param;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Return_;
use PhpParser\Parser;
use Rector\Exception\ShouldNotHappenException;
use Rector\PhpParser\NodeTraverser\CallableNodeTraverser;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * @see https://stackoverflow.com/q/19245205/1348344
 */
final class PregReplaceEModifierRector extends AbstractRector
{
    /**
     * @var Parser
     */
    private $parser;

    /**
     * @var CallableNodeTraverser
     */
    private $callableNodeTraverser;

    public function __construct(Parser $parser, CallableNodeTraverser $callableNodeTraverser)
    {
        $this->parser = $parser;
        $this->callableNodeTraverser = $callableNodeTraverser;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('The /e modifier is no longer supported, use preg_replace_callback instead ', [
            new CodeSample(
                <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $comment = preg_replace('~\b(\w)(\w+)~e', '"$1".strtolower("$2")', $comment);
    }
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $comment = preg_replace_callback('~\b(\w)(\w+)~', function ($matches) {
              return($matches[1].strtolower($matches[2]));
        }, , $comment);
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
        if (! $this->isName($node, 'preg_replace')) {
            return null;
        }

        $pattern = $this->getValue($node->args[0]->value);
        $delimiter = $pattern[0];

        $modifiers = Strings::after($pattern, $delimiter, -1);
        if (! Strings::contains($modifiers, 'e')) {
            return null;
        }

        $modifiersWithoutE = Strings::replace($modifiers, '#e#');
        $patternWithoutE = Strings::before($pattern, $delimiter, -1) . $delimiter . $modifiersWithoutE;

        $anonymousFunction = $this->createAnonymousFunctionFromString($node->args[1]->value);
        if ($anonymousFunction === null) {
            return null;
        }

        $node->name = new Name('preg_replace_callback');
        $node->args[0]->value = new String_($patternWithoutE);
        $node->args[1]->value = $anonymousFunction;

        return $node;
    }

    private function createAnonymousFunctionFromString(Expr $expr): ?Closure
    {
        if (! $expr instanceof String_) {
            // not supported yet
            throw new ShouldNotHappenException();
        }

        $phpCode = '<?php ' . $expr->value . ';';
        $contentNodes = $this->parser->parse($phpCode);

        $anonymousFunction = new Closure();
        if (! $contentNodes[0] instanceof Expression) {
            return null;
        }

        $stmt = $contentNodes[0]->expr;

        $this->callableNodeTraverser->traverseNodesWithCallable([$stmt], function (Node $node) {
            if (! $node instanceof String_) {
                return $node;
            }

            $match = Strings::match($node->value, '#(\\$|\\\\)(?<number>\d+)#');
            if (! $match) {
                return $node;
            }

            $matchesVariable = new Variable('matches');

            return new ArrayDimFetch($matchesVariable, new LNumber((int) $match['number']));
        });

        $anonymousFunction->stmts[] = new Return_($stmt);
        $anonymousFunction->params[] = new Param(new Variable('matches'));

        return $anonymousFunction;
    }
}
