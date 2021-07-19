<?php

namespace Rector\Tests\Php70\Rector\MethodCall\ThisCallOnStaticMethodToStaticCallRector\Fixture;

/**
 * @method static static create($parameters) some description with parameters
 */
trait SkipStringNotMethodNameInAnnotation
{
    public function run()
    {
        $this->parameters();
    }

    public function parameters(): void
    {
    }
}
