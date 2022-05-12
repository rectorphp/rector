<?php

namespace Rector\Tests\Php82\Rector\Class_\ReadOnlyClassRector\Fixture;

final class SkipCombinationWritableReadonly
{
   private readonly string $property;
   private string $property2;
}