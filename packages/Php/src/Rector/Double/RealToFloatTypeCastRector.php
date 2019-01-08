<?php declare(strict_types=1);

namespace Rector\Php\Rector\Double;

use PhpParser\Node;
use PhpParser\Node\Expr\Cast\Double;
use Rector\NodeTypeResolver\Node\Attribute;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * @see https://wiki.php.net/rfc/deprecations_php_7_4
 */
final class RealToFloatTypeCastRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Change deprecated (real) to (float)', [
            new CodeSample(
                <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $number = (real) 5;
        $number = (float) 5;
        $number = (double) 5;
    }
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $number = (float) 5;
        $number = (float) 5;
        $number = (double) 5;
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
        return [Double::class];
    }

    /**
     * @param Double $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($node->getAttribute('kind') !== Double::KIND_REAL) {
            return null;
        }

        $node->setAttribute('kind', Double::KIND_FLOAT);
        $node->setAttribute(Attribute::ORIGINAL_NODE, null);

        return $node;
    }
}
