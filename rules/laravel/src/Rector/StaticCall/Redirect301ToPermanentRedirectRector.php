<?php

declare(strict_types=1);

namespace Rector\Laravel\Rector\StaticCall;

use PhpParser\Node;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;

/**
 * @see https://laravel.com/docs/5.7/upgrade
 * @see \Rector\Laravel\Tests\Rector\StaticCall\Redirect301ToPermanentRedirectRector\Redirect301ToPermanentRedirectRectorTest
 */
final class Redirect301ToPermanentRedirectRector extends AbstractRector
{
    /**
     * @var string[]
     */
    private const ROUTE_TYPES = ['Illuminate\Support\Facades\Route', 'Illuminate\Routing\Route'];

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Change "redirect" call with 301 to "permanentRedirect"', [
            new CodeSample(
                <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        Illuminate\Routing\Route::redirect('/foo', '/bar', 301);
    }
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        Illuminate\Routing\Route::permanentRedirect('/foo', '/bar');
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
        return [StaticCall::class];
    }

    /**
     * @param StaticCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->isObjectTypes($node, self::ROUTE_TYPES)) {
            return null;
        }

        if (! isset($node->args[2])) {
            return null;
        }

        if ($this->getValue($node->args[2]->value) !== 301) {
            return null;
        }

        unset($node->args[2]);

        $node->name = new Identifier('permanentRedirect');

        return $node;
    }
}
