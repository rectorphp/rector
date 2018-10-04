<?php declare(strict_types=1);

namespace Rector\Php\Rector;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
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
        foreach ($this->oldToNewFunctionNames as $oldFunctionName => $newFunctionName) {
            if ((string) $funcCallNode->name === $oldFunctionName) {
                $funcCallNode->name = new Node\Name($newFunctionName);

                // special case: random_int(); → random_int(0, getrandmax());
                if ($newFunctionName === 'random_int' && count($funcCallNode->args) === 0) {
                    $funcCallNode->args[0] = new Node\Arg(new Node\Scalar\LNumber(0));
                    $funcCallNode->args[1] = new Node\Arg(new FuncCall(new Node\Name('mt_getrandmax')));
                }

                return $funcCallNode;
            }
        }

        return $funcCallNode;
    }
}
