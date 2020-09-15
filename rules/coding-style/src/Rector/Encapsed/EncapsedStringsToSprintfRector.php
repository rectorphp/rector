<?php

declare(strict_types=1);

namespace Rector\CodingStyle\Rector\Encapsed;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp\Concat;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\Encapsed;
use PhpParser\Node\Scalar\EncapsedStringPart;
use PhpParser\Node\Scalar\String_;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\NodeTypeResolver\Node\AttributeKey;

/**
 * @see \Rector\CodingStyle\Tests\Rector\Encapsed\EncapsedStringsToSprintfRector\EncapsedStringsToSprintfRectorTest
 */
final class EncapsedStringsToSprintfRector extends AbstractRector
{
    /**
     * @var string
     */
    private $sprintfFormat;

    /**
     * @var Expr[]
     */
    private $argumentVariables = [];

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
        $this->sprintfFormat = '';
        $this->argumentVariables = [];

        foreach ($node->parts as $part) {
            if ($part instanceof EncapsedStringPart) {
                $this->collectEncapsedStringPart($part);
            } elseif ($part instanceof Expr) {
                $this->collectExpr($part);
            }
        }

        return $this->createSprintfFuncCallOrConcat($this->sprintfFormat, $this->argumentVariables);
    }

    private function collectEncapsedStringPart(EncapsedStringPart $encapsedStringPart): void
    {
        $stringValue = $encapsedStringPart->value;
        if ($stringValue === "\n") {
            $this->argumentVariables[] = new ConstFetch(new Name('PHP_EOL'));
            return;
        }

        $this->sprintfFormat .= $stringValue;
    }

    private function collectExpr(Expr $expr): void
    {
        $this->sprintfFormat .= '%s';

        // remove: ${wrap} → $wrap
        if ($expr instanceof Variable) {
            $expr->setAttribute(AttributeKey::ORIGINAL_NODE, null);
        }

        $this->argumentVariables[] = $expr;
    }

    /**
     * @param Expr[] $argumentVariables
     * @return Concat|FuncCall
     */
    private function createSprintfFuncCallOrConcat(string $string, array $argumentVariables): Node
    {
        // special case for variable with PHP_EOL
        if ($string === '%s' && count($argumentVariables) === 2) {
            return new Concat($argumentVariables[0], $argumentVariables[1]);
        }

        $arguments = [new Arg(new String_($string))];
        foreach ($argumentVariables as $argumentVariable) {
            $arguments[] = new Arg($argumentVariable);
        }

        return new FuncCall(new Name('sprintf'), $arguments);
    }
}
