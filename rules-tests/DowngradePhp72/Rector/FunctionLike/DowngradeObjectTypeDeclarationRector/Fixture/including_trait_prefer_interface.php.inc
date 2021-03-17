<?php

namespace Rector\Tests\DowngradePhp72\Rector\FunctionLike\DowngradeObjectTypeDeclarationRector\Fixture;

interface AdapterInterface
{
    public function getItems(array $items);
}

final class SomeAdapter implements AdapterInterface
{
    use BaseAdapterTrait;
}

trait BaseAdapterTrait
{
    public function getItems($items)
    {
    }
}

?>
