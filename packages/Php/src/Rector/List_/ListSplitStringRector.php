<?php declare(strict_types=1);

namespace Rector\Php\Rector\List_;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\List_;
use PhpParser\Node\Name;
use Rector\NodeTypeResolver\NodeTypeAnalyzer;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * @source http://php.net/manual/en/migration70.incompatible.php#migration70.incompatible.variable-handling.list
 *
 * @see https://stackoverflow.com/a/47965344/1348344
 */
final class ListSplitStringRector extends AbstractRector
{
    /**
     * @var NodeTypeAnalyzer
     */
    private $nodeTypeAnalyzer;

    public function __construct(NodeTypeAnalyzer $nodeTypeAnalyzer)
    {
        $this->nodeTypeAnalyzer = $nodeTypeAnalyzer;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'list() cannot split string directly anymore, use str_split()',
            [new CodeSample('list($foo) = "string";', 'list($foo) = str_split("string");')]
        );
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [Assign::class];
    }

    /**
     * @param Assign $assignNode
     */
    public function refactor(Node $assignNode): ?Node
    {
        if (! $assignNode->var instanceof List_) {
            return $assignNode;
        }

        if (! $this->nodeTypeAnalyzer->isStringType($assignNode->expr)) {
            return $assignNode;
        }

        $assignNode->expr = new FuncCall(new Name('str_split'), [new Arg($assignNode->expr)]);

        return $assignNode;
    }
}
