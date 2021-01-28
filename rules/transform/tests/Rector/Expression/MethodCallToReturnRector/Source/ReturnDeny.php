<?php

declare(strict_types=1);

namespace Rector\Transform\Tests\Rector\Expression\MethodCallToReturnRector\Source;

final class ReturnDeny
{
    public function deny()
    {
        return 'error';
    }
}
