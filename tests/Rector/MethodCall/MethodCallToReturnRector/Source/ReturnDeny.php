<?php declare(strict_types=1);

namespace Rector\Core\Tests\Rector\MethodCall\MethodCallToReturnRector\Source;

final class ReturnDeny
{
    public function deny()
    {
        return 'error';
    }
}
