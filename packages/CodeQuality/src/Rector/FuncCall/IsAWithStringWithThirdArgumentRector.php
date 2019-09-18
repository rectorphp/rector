<?php declare(strict_types=1);

namespace Rector\CodeQuality\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\FuncCall;
use PHPStan\Type\StringType;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * @see \Rector\CodeQuality\Tests\Rector\FuncCall\IsAWithStringWithThirdArgumentRector\IsAWithStringWithThirdArgumentRectorTest
 */
final class IsAWithStringWithThirdArgumentRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('', [
            new CodeSample(
                <<<'PHP'
class SomeClass
{
    public function __construct(string $value)
    {
        return is_a($value, 'stdClass');
    }
}
PHP
                ,
                <<<'PHP'
class SomeClass
{
    public function __construct(string $value)
    {
        return is_a($value, 'stdClass', true);
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
        if (! $this->isName($node, 'is_a')) {
            return null;
        }

        if (isset($node->args[2])) {
            return null;
        }

        $firstArgumentStaticType = $this->getStaticType($node->args[0]->value);
        if (! $firstArgumentStaticType instanceof StringType) {
            return null;
        }

        $node->args[2] = new Arg($this->createTrue());

        return $node;
    }
}
