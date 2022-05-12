<?php

namespace Rector\Tests\Php82\Rector\Class_\ReadOnlyClassRector\Fixture;

final class SkipCombinationWritableReadonlyPromotion2
{
   private string $property;

   public function __construct(public readonly string $property2)
   {
   }
}