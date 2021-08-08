<?php

namespace Rector\Tests\DowngradePhp80\Rector\ClassMethod\DowngradeAbstractPrivateMethodInTraitRector\Fixture;

trait SkipAbstractNonPrivate
{
    abstract public function someAbstractPublicFunction();
    abstract protected function someAbstractProtectedFunction();
}
?>
