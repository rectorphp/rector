<?php

namespace Rector\Tests\Php82\Rector\Class_\ReadOnlyClassRector\Fixture;

final class SkipCombinationWritableReadonly
{
   private string $property;
   public readonly string $property2;
}
