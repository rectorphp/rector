<?php

declare(strict_types=1);

namespace Rector\Tests\DowngradePhp80\Rector\ClassMethod\DowngradeStaticTypeDeclarationRector\Fixture;

final class SkipSelf
{
    public function run(): self
    {
        return $this;
    }
}
