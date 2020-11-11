<?php

declare(strict_types=1);

namespace Rector\Laravel\Rector\Class_;

use PhpParser\Node;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;

/**
 * @see https://laracasts.com/discuss/channels/laravel/laravel-57-upgrade-observer-problem
 *
 * @see \Rector\Laravel\Tests\Rector\Class_\AddParentBootToModelClassMethodRector\AddParentBootToModelClassMethodRectorTest
 */
final class AddParentBootToModelClassMethodRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Add parent::boot(); call to boot() class method in child of Illuminate\Database\Eloquent\Model', [
            new CodeSample(
                <<<'PHP'
use Illuminate\Database\Eloquent\Model

class Product extends Model
{
    public function boot()
    {
    }
}
PHP

                ,
                <<<'PHP'
use Illuminate\Database\Eloquent\Model

class Product extends Model
{
    public function boot()
    {
        parent::boot();
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
        return [\PhpParser\Node\Stmt\Class_::class];
    }

    /**
     * @param \PhpParser\Node\Stmt\Class_ $node
     */
    public function refactor(Node $node): ?Node
    {
        // change the node

        return $node;
    }
}
