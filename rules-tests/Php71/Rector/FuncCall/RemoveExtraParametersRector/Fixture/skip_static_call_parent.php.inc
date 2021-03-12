<?php

namespace Rector\Tests\Php71\Rector\FuncCall\RemoveExtraParametersRector\Fixture;

use Rector\Tests\Php71\Rector\FuncCall\RemoveExtraParametersRector\Source\ChildOrmion;

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
