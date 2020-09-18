<?php

namespace Rector\Php70\Tests\Rector\FuncCall\NonVariableToVariableOnFunctionCallRector\Fixture;

final class KeepCurlyBraces
{
    /** @var array|null */
    private $models;

    public function run(): void
    {
        foreach ($this->models as $model) {
            $methods = (new \ReflectionClass($model))->getMethods();

            $model = new $model();

            foreach ($methods as $method) {
                $model->{$method->getName()}();
            }
        }
    }
}
