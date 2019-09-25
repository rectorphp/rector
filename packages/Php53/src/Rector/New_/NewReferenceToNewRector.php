<?php declare(strict_types=1);

namespace Rector\Php53\Rector\New_;

use PhpParser\Node;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * @see https://www.php.net/manual/en/language.operators.assignment.php#language.operators.assignment.reference
 */

/**
 * @see \Rector\Php53\Tests\Rector\New_\NewReferenceToNewRector\NewReferenceToNewRectorTest
 */
final class NewReferenceToNewRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Remove & from new &X', [
            new CodeSample(
                <<<'PHP'
class SomeClass
{
    public function run()
    {
        $value = &new stdClass;
    }
}
PHP
,
                <<<'PHP'
class SomeClass
{
    public function run()
    {
        $value = new stdClass;
    }
}
PHP

            )
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [\PhpParser\Node\Expr\New_::class];
    }

    /**
     * @param \PhpParser\Node\Expr\New_ $node
     */
    public function refactor(Node $node): ?Node
    {
        // change the node

        return $node;
    }
}
