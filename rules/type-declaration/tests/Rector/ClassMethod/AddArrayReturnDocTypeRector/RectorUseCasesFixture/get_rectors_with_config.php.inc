<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddArrayReturnDocTypeRector\RectorUseCasesFixture;

use Rector\Doctrine\Rector\Class_\AddEntityIdByConditionRector;
use Rector\Doctrine\Tests\Rector\Class_\AddEntityIdByConditionRector\Source\SomeTrait;

class GetRectorsWithConfig
{
    protected function getRectorsWithConfiguration(): array
    {
        return [
            AddEntityIdByConditionRector::class => [
                AddEntityIdByConditionRector::DETECTED_TRAITS => [SomeTrait::class],
            ],
        ];
    }
}
?>
-----
<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddArrayReturnDocTypeRector\RectorUseCasesFixture;

use Rector\Doctrine\Rector\Class_\AddEntityIdByConditionRector;
use Rector\Doctrine\Tests\Rector\Class_\AddEntityIdByConditionRector\Source\SomeTrait;

class GetRectorsWithConfig
{
    /**
     * @return array<string, array<string, string[]>>
     */
    protected function getRectorsWithConfiguration(): array
    {
        return [
            AddEntityIdByConditionRector::class => [
                AddEntityIdByConditionRector::DETECTED_TRAITS => [SomeTrait::class],
            ],
        ];
    }
}
?>
