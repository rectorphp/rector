<?php

namespace Rector\NodeTypeResolver\Tests\NodeVisitor\FunctionMethodAndClassNodeVisitor\Fixture;

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
