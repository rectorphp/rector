<?php

namespace Rector\Tests\Php82\Rector\Class_\ReadOnlyClassRector\Fixture;

final class SkipPropertyPromotionWritable
{
    public function __construct(private string $data)
    {
    }
}
