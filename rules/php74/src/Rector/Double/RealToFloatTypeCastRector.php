<?php

declare(strict_types=1);

namespace Rector\Php74\Rector\Double;

use PhpParser\Node;
use PhpParser\Node\Expr\Cast\Double;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\NodeTypeResolver\Node\AttributeKey;

/**
 * @see https://wiki.php.net/rfc/deprecations_php_7_4
 * @see \Rector\Php74\Tests\Rector\Double\RealToFloatTypeCastRector\RealToFloatTypeCastRectorTest
 */
final class RealToFloatTypeCastRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Change deprecated (real) to (float)', [
            new CodeSample(
                <<<'PHP'
class SomeClass
{
    public function run()
    {
        $number = (real) 5;
        $number = (float) 5;
        $number = (double) 5;
    }
}
PHP
                ,
                <<<'PHP'
class SomeClass
{
    public function run()
    {
        $number = (float) 5;
        $number = (float) 5;
        $number = (double) 5;
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
        return [Double::class];
    }

    /**
     * @param double $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($node->getAttribute(AttributeKey::KIND) !== Double::KIND_REAL) {
            return null;
        }

        $node->setAttribute(AttributeKey::KIND, Double::KIND_FLOAT);
        $node->setAttribute(AttributeKey::ORIGINAL_NODE, null);

        return $node;
    }
}
