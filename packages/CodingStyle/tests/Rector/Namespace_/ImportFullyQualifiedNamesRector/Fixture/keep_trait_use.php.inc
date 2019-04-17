<?php

namespace Rector\CodingStyle\Tests\Rector\Namespace_\ImportFullyQualifiedNamesRector\Fixture;

final class KeepTraitUse
{
    use SomeTraitToBeUsedTrait;
}

trait SomeTraitToBeUsedTrait
{

}
