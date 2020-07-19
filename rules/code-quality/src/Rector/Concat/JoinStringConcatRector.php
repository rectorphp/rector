<?php

declare(strict_types=1);

namespace Rector\CodeQuality\Rector\Concat;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp\Concat;
use PhpParser\Node\Scalar\String_;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;

/**
 * @see \Rector\CodeQuality\Tests\Rector\Concat\JoinStringConcatRector\JoinStringConcatRectorTest
 */
final class JoinStringConcatRector extends AbstractRector
{
    /**
     * @var int
     */
    private const LINE_BREAK_POINT = 100;

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Joins concat of 2 strings, unless the lenght is too long', [
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
        $originalNode = clone $node;

        $joinedNode = $this->joinConcatIfStrings($node);
        if (! $joinedNode instanceof String_) {
            return null;
        }

        // the value is too long
        if (Strings::length($joinedNode->value) >= self::LINE_BREAK_POINT) {
            // this prevents keeping joins
            return $originalNode;
        }

        return $joinedNode;
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

        return new String_($concat->left->value . $concat->right->value);
    }
}
