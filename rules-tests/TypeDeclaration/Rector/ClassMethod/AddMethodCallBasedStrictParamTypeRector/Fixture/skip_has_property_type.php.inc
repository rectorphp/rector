<?php

declare(strict_types=1);

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\AddMethodCallBasedStrictParamTypeRector\Fixture;

final class SkipHasPropertyType
{
    /**
     * @param mixed $data
     */
    public function run($data)
    {
        if (! isset($data->type)) {
            return;
        }

        if ($data->type === 'value') {
            $this->runData($data);
        }
    }

    private function runData($data)
    {
    }
}
