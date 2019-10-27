<?php declare(strict_types=1);

namespace Rector\Tests\Rector\MethodCall\MethodCallToReturnRector\Source;

final class ReturnDeny
{
    public function deny()
    {
        return 'error';
    }
}
