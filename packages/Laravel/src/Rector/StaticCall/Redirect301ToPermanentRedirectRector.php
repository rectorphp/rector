<?php declare(strict_types=1);

namespace Rector\Laravel\Rector\StaticCall;

use PhpParser\Node;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * @see https://laravel.com/docs/5.7/upgrade
 */
final class Redirect301ToPermanentRedirectRector extends AbstractRector
{
    /**
     * @var string[]
     */
    private $routeTypes = ['Illuminate\Support\Facades\Route', 'Illuminate\Routing\Route'];

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
        if (! $this->isTypes($node, $this->routeTypes)) {
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
