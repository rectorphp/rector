<?php declare(strict_types=1);

namespace Rector\Laravel\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * @see https://laravel.com/docs/5.8/upgrade#string-and-array-helpers
 */

/**
 * @see \Rector\Laravel\Tests\Rector\FuncCall\ArrayHelperToIlluminateSupportClassRector\ArrayHelperToIlluminateSupportClassRectorTest
 */
final class ArrayHelperToIlluminateSupportClassRector extends AbstractRector
{
    private $helpersToFunction = [
        'array_add' => 'add',
        'array_collapse' => 'collapse',
        'array_divide' => 'divide',
        'array_dot' => 'dot',
        'array_except' => 'except',
        'array_first' => 'first',
        'array_flatten' => 'flatten',
        'array_forget' => 'forget',
        'array_get' => 'get',
        'array_has' => 'has',
        'array_last' => 'last',
        'array_only' => 'only',
        'array_pluck' => 'pluck',
        'array_prepend' => 'prepend',
        'array_pull' => 'pull',
        'array_random' => 'random',
        'array_set' => 'set',
        'array_sort' => 'sort',
        'array_sort_recursive' => 'sortRecursive',
        'array_where' => 'where',
        'array_wrap' => 'wrap',
    ];

    private $supportClass = '\Illuminate\Support\Arr';

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Use the Illuminate Support Arr class instead of the array helper functions', [
            new CodeSample(
                <<<'CODE_SAMPLE'
class SomeClass
{
    public function run($data)
    {
        array_add($data, 'field', 'value');
        array_collapse($data);
        array_divide($data);
        array_dot($data);
        array_dot($data, '-');
        array_except($data, 'ignore');
        array_first($data);
        array_first($data, static function ($value) {$value > 5;});
        array_first($data, static function ($value) {$value > 5;}, 10);
        array_flatten($data);
        array_flatten($data, 5);
        array_forget($data, 'forget_this');
        array_get($data, 'field');
        array_get($data, 'field', 'default');
        array_has($data, 'needed');
        array_last($data);
        array_last($data, static function ($value) {$value > 5;});
        array_last($data, static function ($value) {$value > 5;}, 10);
        array_only($data, 'key');
        array_pluck($data, 'value');
        array_pluck($data, 'value', 'key');
        array_prepend($data, 'value');
        array_prepend($data, 'value', 'key');
        array_pull($data, 'value');
        array_pull($data, 'value', 'key');
        array_random($data);
        array_random($data, 5);
        array_set($data, 'key', 'value');
        array_sort($data);
        array_sort($data, static function ($value) {return strrev($value);});
        array_sort_recursive($data);
        array_where($data, static function ($value) { return $value > 5;});
        array_wrap($data);
    }
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
class SomeClass
{
    public function run($data)
    {
        \Illuminate\Support\Arr::add($data, 'field', 'value');
        \Illuminate\Support\Arr::collapse($data);
        \Illuminate\Support\Arr::divide($data);
        \Illuminate\Support\Arr::dot($data);
        \Illuminate\Support\Arr::dot($data, '-');
        \Illuminate\Support\Arr::except($data, 'ignore');
        \Illuminate\Support\Arr::first($data);
        \Illuminate\Support\Arr::first($data, static function ($value) {$value > 5;});
        \Illuminate\Support\Arr::first($data, static function ($value) {$value > 5;}, 10);
        \Illuminate\Support\Arr::flatten($data);
        \Illuminate\Support\Arr::flatten($data, 5);
        \Illuminate\Support\Arr::forget($data, 'forget_this');
        \Illuminate\Support\Arr::get($data, 'field');
        \Illuminate\Support\Arr::get($data, 'field', 'default');
        \Illuminate\Support\Arr::has($data, 'needed');
        \Illuminate\Support\Arr::last($data);
        \Illuminate\Support\Arr::last($data, static function ($value) {$value > 5;});
        \Illuminate\Support\Arr::last($data, static function ($value) {$value > 5;}, 10);
        \Illuminate\Support\Arr::only($data, 'key');
        \Illuminate\Support\Arr::pluck($data, 'value');
        \Illuminate\Support\Arr::pluck($data, 'value', 'key');
        \Illuminate\Support\Arr::prepend($data, 'value');
        \Illuminate\Support\Arr::prepend($data, 'value', 'key');
        \Illuminate\Support\Arr::pull($data, 'value');
        \Illuminate\Support\Arr::pull($data, 'value', 'key');
        \Illuminate\Support\Arr::random($data);
        \Illuminate\Support\Arr::random($data, 5);
        \Illuminate\Support\Arr::set($data, 'key', 'value');
        \Illuminate\Support\Arr::sort($data);
        \Illuminate\Support\Arr::sort($data, static function ($value) {return strrev($value);});
        \Illuminate\Support\Arr::sortRecursive($data);
        \Illuminate\Support\Arr::where($data, static function ($value) { return $value > 5;});
        \Illuminate\Support\Arr::wrap($data);
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
        return [FuncCall::class];
    }

    /**
     * @param FuncCall $node
     */
    public function refactor(Node $node): ?Node
    {
        foreach ($this->helpersToFunction as $helperFunction => $classFunction) {
            if (! $this->isName($node, $helperFunction)) {
                continue;
            }

            return new StaticCall(new Name($this->supportClass), new Identifier($classFunction), $node->args);
        }
        return $node;
    }
}
