<?php

declare(strict_types=1);

namespace Rector\Php70\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\LNumber;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;
use Rector\ValueObject\PhpVersionFeature;

/**
 * @see \Rector\Php70\Tests\Rector\FuncCall\RandomFunctionRector\RandomFunctionRectorTest
 */
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
            'Changes rand, srand and getrandmax by new mt_* alternatives.',
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
     * @param FuncCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->isAtLeastPhpVersion(PhpVersionFeature::CSPRNG_FUNCTIONS)) {
            return null;
        }

        foreach ($this->oldToNewFunctionNames as $oldFunctionName => $newFunctionName) {
            if ($this->isName($node, $oldFunctionName)) {
                $node->name = new Name($newFunctionName);

                // special case: random_int(); → random_int(0, getrandmax());
                if ($newFunctionName === 'random_int' && count($node->args) === 0) {
                    $node->args[0] = new Arg(new LNumber(0));
                    $node->args[1] = new Arg($this->createFunction('mt_getrandmax'));
                }

                return $node;
            }
        }

        return $node;
    }
}
