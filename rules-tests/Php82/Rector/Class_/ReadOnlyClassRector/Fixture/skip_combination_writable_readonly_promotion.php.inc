<?php

namespace Rector\Tests\Php82\Rector\Class_\ReadOnlyClassRector\Fixture;

final class SkipCombinationWritableReadonlyPromotion
{
   private string $property;

   public function __construct(private readonly string $property2)
   {
   }
}