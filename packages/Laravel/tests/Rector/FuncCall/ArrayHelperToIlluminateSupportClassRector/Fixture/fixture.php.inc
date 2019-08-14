<?php

namespace Rector\Laravel\Tests\Rector\FuncCall\ArrayHelperToIlluminateSupportClassRector\Fixture;

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

?>
-----
<?php

namespace Rector\Laravel\Tests\Rector\FuncCall\ArrayHelperToIlluminateSupportClassRector\Fixture;

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

?>
