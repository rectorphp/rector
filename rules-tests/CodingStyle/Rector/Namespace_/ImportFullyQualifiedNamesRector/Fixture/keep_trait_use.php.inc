<?php

namespace Rector\Tests\CodingStyle\Rector\Namespace_\ImportFullyQualifiedNamesRector\Fixture;

final class KeepTraitUse
{
    use SomeTraitToBeUsedTrait;
}

trait SomeTraitToBeUsedTrait
{

}
