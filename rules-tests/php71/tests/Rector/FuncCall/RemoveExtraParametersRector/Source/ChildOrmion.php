<?php

declare(strict_types=1);

namespace Rector\Php71\Tests\Rector\FuncCall\RemoveExtraParametersRector\Source;

final class ChildOrmion extends Ormion
{
    public static function getDb(): Db
    {
        return new Db();
    }

    /**
     * @return Db
     */
    public static function getDbWithAnnotationReturn()
    {
        return new Db();
    }
}
