<?php

namespace Rector\Tests\Php82\Rector\Class_\ReadOnlyClassRector\Fixture;

#[\AllowDynamicProperties]
final class SkipAllowDynamic
{
   private readonly string $property;
}
