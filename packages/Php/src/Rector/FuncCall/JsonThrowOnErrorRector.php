<?php declare(strict_types=1);

namespace Rector\Php\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * @see http://wiki.php.net/rfc/json_throw_on_error
 */
final class JsonThrowOnErrorRector extends AbstractRector
{
    /**
     * @var int[]
     */
    private $functionsToConstantPositions = [
        'json_encode' => 1,
        'json_decode' => 3,
    ];

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Adds JSON_THROW_ON_ERROR to json_encode() and json_decode() to throw JsonException on error',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
json_encode($content);
json_decode($json);
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
json_encode($content, JSON_THROW_ON_ERROR
json_decode($json, null, null, JSON_THROW_ON_ERROR););
CODE_SAMPLE
                ),
            ]
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
        if (! $this->isNames($node, array_keys($this->functionsToConstantPositions))) {
            return null;
        }

        $constantPosition = $this->functionsToConstantPositions[$this->getName($node)];

        for ($i = 0; $i <= $constantPosition; ++$i) {
            if (isset($node->args[$i])) {
                continue;
            }

            if ($i === $constantPosition) {
                $node->args[$i] = new Arg($this->createConstFetch('JSON_THROW_ON_ERROR'));
            } else {
                // fill in blanks
                $node->args[$i] = new Arg($this->createConstFetch('null'));
            }
        }

        return $node;
    }

    private function createConstFetch(string $name): ConstFetch
    {
        return new ConstFetch(new Name($name));
    }
}
