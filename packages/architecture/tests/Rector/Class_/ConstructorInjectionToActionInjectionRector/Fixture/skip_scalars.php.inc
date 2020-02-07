<?php

namespace Rector\Architecture\Tests\Rector\Class_\ConstructorInjectionToActionInjectionRector\Fixture;

final class SkipScalarsController
{
    /**
     * @var mixed[]
     */
    private $items = [];

    public function __construct(array $items)
    {
        $this->items = $items;
    }

    public function actionInjection()
    {
        $item = $this->items[5];
    }
}
