<?php

declare(strict_types=1);

use Rector\Generic\Rector\FuncCall\FuncCallToStaticCallRector;
use function Rector\SymfonyPhpConfig\inline_value_objects;
use Rector\Transform\ValueObject\FuncNameToStaticCallName;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(FuncCallToStaticCallRector::class)
        ->call('configure', [[
            FuncCallToStaticCallRector::FUNC_CALLS_TO_STATIC_CALLS => inline_value_objects([
                new FuncNameToStaticCallName('array_add', 'Illuminate\Support\Arr', 'add'),
                new FuncNameToStaticCallName('array_collapse', 'Illuminate\Support\Arr', 'collapse'),
                new FuncNameToStaticCallName('array_divide', 'Illuminate\Support\Arr', 'divide'),
                new FuncNameToStaticCallName('array_dot', 'Illuminate\Support\Arr', 'dot'),
                new FuncNameToStaticCallName('array_except', 'Illuminate\Support\Arr', 'except'),
                new FuncNameToStaticCallName('array_first', 'Illuminate\Support\Arr', 'first'),
                new FuncNameToStaticCallName('array_flatten', 'Illuminate\Support\Arr', 'flatten'),
                new FuncNameToStaticCallName('array_forget', 'Illuminate\Support\Arr', 'forget'),
                new FuncNameToStaticCallName('array_get', 'Illuminate\Support\Arr', 'get'),
                new FuncNameToStaticCallName('array_has', 'Illuminate\Support\Arr', 'has'),
                new FuncNameToStaticCallName('array_last', 'Illuminate\Support\Arr', 'last'),
                new FuncNameToStaticCallName('array_only', 'Illuminate\Support\Arr', 'only'),
                new FuncNameToStaticCallName('array_pluck', 'Illuminate\Support\Arr', 'pluck'),
                new FuncNameToStaticCallName('array_prepend', 'Illuminate\Support\Arr', 'prepend'),
                new FuncNameToStaticCallName('array_pull', 'Illuminate\Support\Arr', 'pull'),
                new FuncNameToStaticCallName('array_random', 'Illuminate\Support\Arr', 'random'),
                new FuncNameToStaticCallName('array_sort', 'Illuminate\Support\Arr', 'sort'),
                new FuncNameToStaticCallName('array_sort_recursive', 'Illuminate\Support\Arr', 'sortRecursive'),
                new FuncNameToStaticCallName('array_where', 'Illuminate\Support\Arr', 'where'),
                new FuncNameToStaticCallName('array_wrap', 'Illuminate\Support\Arr', 'wrap'),
                new FuncNameToStaticCallName('array_set', 'Illuminate\Support\Arr', 'set'),
                new FuncNameToStaticCallName('camel_case', 'Illuminate\Support\Str', 'camel'),
                new FuncNameToStaticCallName('ends_with', 'Illuminate\Support\Str', 'endsWith'),
                new FuncNameToStaticCallName('kebab_case', 'Illuminate\Support\Str', 'kebab'),
                new FuncNameToStaticCallName('snake_case', 'Illuminate\Support\Str', 'snake'),
                new FuncNameToStaticCallName('starts_with', 'Illuminate\Support\Str', 'startsWith'),
                new FuncNameToStaticCallName('str_after', 'Illuminate\Support\Str', 'after'),
                new FuncNameToStaticCallName('str_before', 'Illuminate\Support\Str', 'before'),
                new FuncNameToStaticCallName('str_contains', 'Illuminate\Support\Str', 'contains'),
                new FuncNameToStaticCallName('str_finish', 'Illuminate\Support\Str', 'finish'),
                new FuncNameToStaticCallName('str_is', 'Illuminate\Support\Str', 'is'),
                new FuncNameToStaticCallName('str_limit', 'Illuminate\Support\Str', 'limit'),
                new FuncNameToStaticCallName('str_plural', 'Illuminate\Support\Str', 'plural'),
                new FuncNameToStaticCallName('str_random', 'Illuminate\Support\Str', 'random'),
                new FuncNameToStaticCallName('str_replace_array', 'Illuminate\Support\Str', 'replaceArray'),
                new FuncNameToStaticCallName('str_replace_first', 'Illuminate\Support\Str', 'replaceFirst'),
                new FuncNameToStaticCallName('str_replace_last', 'Illuminate\Support\Str', 'replaceLast'),
                new FuncNameToStaticCallName('str_singular', 'Illuminate\Support\Str', 'singular'),
                new FuncNameToStaticCallName('str_slug', 'Illuminate\Support\Str', 'slug'),
                new FuncNameToStaticCallName('str_start', 'Illuminate\Support\Str', 'start'),
                new FuncNameToStaticCallName('studly_case', 'Illuminate\Support\Str', 'studly'),
                new FuncNameToStaticCallName('title_case', 'Illuminate\Support\Str', 'title'),
            ]),
        ]]);
};
