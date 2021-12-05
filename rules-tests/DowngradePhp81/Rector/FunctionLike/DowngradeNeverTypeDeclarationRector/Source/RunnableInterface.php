<?php

namespace Rector\Tests\DowngradePhp81\Rector\FunctionLike\DowngradeNeverTypeDeclarationRector\Source;

interface RunnableInterface
{
    public function run(): ?\stdClass;
}
