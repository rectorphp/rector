<?php

namespace Rector\Renaming\Tests\Rector\MethodCall\RenameMethodRector\Fixture;

class UnderAnonymousClass
{
    public function testErrorHandler(): void
    {
        $this->expectException(\ErrorException::class);

        $class = new class {
            public function triggerError(): void
            {
                $this->startErrorHandling();
                trigger_error('an error has occurred');
                $this->stopErrorHandling();
            }
        };

        $class->triggerError();
    }
}
