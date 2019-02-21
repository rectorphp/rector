<?php

namespace Rector\Php\Tests\Rector\FuncCall\RemoveExtraParametersRector\Fixture;

use Rector\Php\Tests\Rector\FuncCall\RemoveExtraParametersRector\Source\ChildOrmion;

class StaticCallParent
{
    public function someMethod()
    {
        ChildOrmion::delete();
        ChildOrmion::getDb()->delete(2);

        ChildOrmion::getDbWithAnnotationReturn()
            ->delete(2);
    }
}
