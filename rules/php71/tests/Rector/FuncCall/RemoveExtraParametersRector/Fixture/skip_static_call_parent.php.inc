<?php

namespace Rector\Php71\Tests\Rector\FuncCall\RemoveExtraParametersRector\Fixture;

use Rector\Php71\Tests\Rector\FuncCall\RemoveExtraParametersRector\Source\ChildOrmion;

class SkipStaticCallParent
{
    public function someMethod()
    {
        ChildOrmion::delete();
        ChildOrmion::getDb()->delete(2);

        ChildOrmion::getDbWithAnnotationReturn()
            ->delete(2);
    }
}
