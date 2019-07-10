<?php declare(strict_types=1);

namespace Rector\CodingStyle\Rector\Encapsed;

use PhpParser\Node;
use PhpParser\Node\Scalar\Encapsed;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * @see \Rector\CodingStyle\Tests\Rector\Encapsed\EncapsedStringsToSprintfRector\EncapsedStringsToSprintfRectorTest
 */
final class EncapsedStringsToSprintfRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Convert enscaped {$string} to more readable sprintf', [
            new CodeSample(
                <<<'CODE_SAMPLE'
final class SomeClass
{
    public function run(string $format)
    {
        return "Unsupported format {$format}";
    }
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
final class SomeClass
{
    public function run(string $format)
    {
        return sprintf('Unsupported format %s', $format);
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
        return [Encapsed::class];
    }

    /**
     * @param Encapsed $node
     */
    public function refactor(Node $node): ?Node
    {
        $string = '';
        $arguments = [];

        foreach ($node->parts as $part) {
            if ($part instanceof Node\Scalar\EncapsedStringPart) {
                $string .= $part->value;
                continue;
            }

            if ($part instanceof Node\Expr\Variable) {
                $string .= '%s';
                $arguments[] = new Node\Arg($part);
            }
        }

        $arguments = array_merge([new Node\Arg(new Node\Scalar\String_($string))], $arguments);

        return new Node\Expr\FuncCall(new Node\Name('sprintf'), $arguments);
    }
}
