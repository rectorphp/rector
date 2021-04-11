<?php

namespace Rector\Tests\CodeQualityStrict\Rector\Variable\MoveVariableDeclarationNearReferenceRector\Fixture;

final class SkipPassJsonLastError
{
    public function run($data, $options)
    {
        $result = json_encode($data, $options, 512);

        if (json_last_error()) {
            throw new \Exception();
        }

        echo $result;
    }
}