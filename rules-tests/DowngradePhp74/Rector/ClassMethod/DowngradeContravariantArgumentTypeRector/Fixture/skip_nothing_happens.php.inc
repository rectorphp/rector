<?php

namespace Rector\Tests\DowngradePhp74\Rector\ClassMethod\DowngradeContravariantArgumentTypeRector\Fixture;

class NothingHappensType {}

class NothingHappensA
{
    public function contraVariantArguments(NothingHappensType $type)
    { /* … */ }
}

class NothingHappensB extends NothingHappensA
{
    public function contraVariantArguments(NothingHappensType $type)
    { /* … */ }
}

?>
