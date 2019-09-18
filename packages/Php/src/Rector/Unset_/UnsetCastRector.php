<?php declare(strict_types=1);

namespace Rector\Php\Rector\Unset_;

use PhpParser\Node;
use PhpParser\Node\Expr\Cast\Unset_;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * @see \Rector\Php\Tests\Rector\Unset_\UnsetCastRector\UnsetCastRectorTest
 */
final class UnsetCastRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Removes (unset) cast', [
            new CodeSample(
                <<<'PHP'
$value = (unset) $value;
PHP
                ,
                <<<'PHP'
$value = null;
PHP
            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [Unset_::class];
    }

    /**
     * @param Unset_ $node
     */
    public function refactor(Node $node): ?Node
    {
        return $this->createNull();
    }
}
