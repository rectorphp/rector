<?php

declare(strict_types=1);

namespace Rector\Generic\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Name\FullyQualified;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;

/**
 * @see \Rector\Generic\Tests\Rector\FuncCall\FunctionToNewRector\FunctionToNewRectorTest
 */
final class FunctionToNewRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @var string
     */
    public const FUNCTION_TO_NEW = '$functionToNew';

    /**
     * @var string[]
     */
    private $functionToNew = [];

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Change configured function calls to new Instance', [
            new CodeSample(
                <<<'PHP'
class SomeClass
{
    public function run()
    {
        $array = collection([]);
    }
}
PHP
                ,
                <<<'PHP'
class SomeClass
{
    public function run()
    {
        $array = new \Collection([]);
    }
}
PHP
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
        foreach ($this->functionToNew as $function => $new) {
            if (! $this->isName($node, $function)) {
                continue;
            }

            return new New_(new FullyQualified($new), $node->args);
        }

        return null;
    }

    public function configure(array $configuration): void
    {
        $this->functionToNew = $configuration[self::FUNCTION_TO_NEW] ?? [];
    }
}
