<?php

declare(strict_types=1);

namespace Rector\CodeQuality\Rector\Concat;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp\Concat;
use PhpParser\Node\Scalar\String_;
use Rector\CodeQuality\Exception\TooLongException;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * @see \Rector\CodeQuality\Tests\Rector\Concat\JoinStringConcatRector\JoinStringConcatRectorTest
 */
final class JoinStringConcatRector extends AbstractRector
{
    /**
     * @var int
     */
    private const LINE_BREAK_POINT = 80;

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Joins concat of 2 strings', [
            new CodeSample(
                <<<'PHP'
class SomeClass
{
    public function run()
    {
        $name = 'Hi' . ' Tom';
    }
}
PHP
                ,
                <<<'PHP'
class SomeClass
{
    public function run()
    {
        $name = 'Hi Tom';
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
        return [Concat::class];
    }

    /**
     * @param Concat $node
     */
    public function refactor(Node $node): ?Node
    {
        try {
            return $this->joinConcatIfStrings($node);
        } catch (TooLongException $tooLongException) {
            return null;
        }
    }

    /**
     * @return Concat|String_
     */
    private function joinConcatIfStrings(Concat $concat): Node
    {
        if ($concat->left instanceof Concat) {
            $concat->left = $this->joinConcatIfStrings($concat->left);
        }

        if ($concat->right instanceof Concat) {
            $concat->right = $this->joinConcatIfStrings($concat->right);
        }

        if (! $concat->left instanceof String_) {
            return $concat;
        }

        if (! $concat->right instanceof String_) {
            return $concat;
        }

        $value = $concat->left->value . $concat->right->value;
        if (Strings::length($value) >= self::LINE_BREAK_POINT) {
            throw new TooLongException();
        }

        return new String_($concat->left->value . $concat->right->value);
    }
}
