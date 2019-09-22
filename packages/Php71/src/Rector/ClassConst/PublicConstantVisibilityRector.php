<?php declare(strict_types=1);

namespace Rector\Php71\Rector\ClassConst;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassConst;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * @see https://wiki.php.net/rfc/class_const_visibility
 *
 * @see \Rector\Php\Tests\Rector\ClassConst\PublicConstantVisibilityRector\PublicConstantVisibilityRectorTest
 */
final class PublicConstantVisibilityRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Add explicit public constant visibility.',
            [
                new CodeSample(
                    <<<'PHP'
class SomeClass
{
    const HEY = 'you';
}
PHP
                    ,
                    <<<'PHP'
class SomeClass
{
    public const HEY = 'you';
}
PHP
                ),
            ]
        );
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [ClassConst::class];
    }

    /**
     * @param ClassConst $node
     */
    public function refactor(Node $node): ?Node
    {
        // already non-public
        if (! $node->isPublic()) {
            return null;
        }

        // explicitly public
        if ($node->flags !== 0) {
            return null;
        }

        $this->makePublic($node);

        return $node;
    }
}
