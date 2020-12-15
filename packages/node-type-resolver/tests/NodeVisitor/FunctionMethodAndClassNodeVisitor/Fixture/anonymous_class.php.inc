<?php

namespace Rector\NodeTypeResolver\Tests\NodeVisitor\FunctionMethodAndClassNodeVisitorTest\Fixture;

class AnonymousClass
{
    public function bar()
    {
        new class
        {
            public function baz()
            {
            }
        };
        moreCode();
    }
}
