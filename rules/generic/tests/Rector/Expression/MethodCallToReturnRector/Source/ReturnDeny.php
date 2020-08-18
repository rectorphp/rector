<?php declare(strict_types=1);

namespace Rector\Generic\Tests\Rector\Expression\MethodCallToReturnRector\Source;

final class ReturnDeny
{
    public function deny()
    {
        return 'error';
    }
}
