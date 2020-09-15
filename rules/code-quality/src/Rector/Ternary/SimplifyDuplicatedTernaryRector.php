<?php

declare(strict_types=1);

namespace Rector\CodeQuality\Rector\Ternary;

use PhpParser\Node;
use PhpParser\Node\Expr\Ternary;
use PHPStan\Type\BooleanType;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;

/**
 * @see \Rector\CodeQuality\Tests\Rector\Ternary\SimplifyDuplicatedTernaryRector\SimplifyDuplicatedTernaryRectorTest
 */
final class SimplifyDuplicatedTernaryRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Remove ternary that duplicated return value of true : false', [
            new CodeSample(
                <<<'CODE_SAMPLE'
class SomeClass
{
    public function run(bool $value, string $name)
    {
         $isTrue = $value ? true : false;
         $isName = $name ? true : false;
    }
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
class SomeClass
{
    public function run(bool $value, string $name)
    {
         $isTrue = $value;
         $isName = $name ? true : false;
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
        return [Ternary::class];
    }

    /**
     * @param Ternary $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->isStaticType($node->cond, BooleanType::class)) {
            return null;
        }

        if ($node->if === null) {
            return null;
        }

        if (! $this->isTrue($node->if) || ! $this->isFalse($node->else)) {
            return null;
        }

        return $node->cond;
    }
}
