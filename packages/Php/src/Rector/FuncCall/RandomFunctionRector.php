<?php declare(strict_types=1);

namespace Rector\Php\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\LNumber;
use Rector\NodeAnalyzer\CallAnalyzer;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

final class RandomFunctionRector extends AbstractRector
{
    /**
     * @var string[]
     */
    private $oldToNewFunctionNames = [
        'getrandmax' => 'mt_getrandmax',
        'srand' => 'mt_srand',
        'mt_rand' => 'random_int',
        'rand' => 'random_int',
    ];

    /**
     * @var CallAnalyzer
     */
    private $callAnalyzer;

    public function __construct(CallAnalyzer $callAnalyzer)
    {
        $this->callAnalyzer = $callAnalyzer;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Changes rand, srand and getrandmax by new md_* alternatives.',
            [new CodeSample('rand();', 'mt_rand();')]
        );
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [FuncCall::class];
    }

    /**
     * @param FuncCall $funcCallNode
     */
    public function refactor(Node $funcCallNode): ?Node
    {
        $currentFunction = $this->callAnalyzer->resolveName($funcCallNode);

        foreach ($this->oldToNewFunctionNames as $oldFunctionName => $newFunctionName) {
            if ($currentFunction === $oldFunctionName) {
                $funcCallNode->name = new Name($newFunctionName);

                // special case: random_int(); → random_int(0, getrandmax());
                if ($newFunctionName === 'random_int' && count($funcCallNode->args) === 0) {
                    $funcCallNode->args[0] = new Arg(new LNumber(0));
                    $funcCallNode->args[1] = new Arg(new FuncCall(new Name('mt_getrandmax')));
                }

                return $funcCallNode;
            }
        }

        return $funcCallNode;
    }
}
