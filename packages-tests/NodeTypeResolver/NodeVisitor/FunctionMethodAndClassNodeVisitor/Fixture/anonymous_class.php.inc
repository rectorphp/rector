<?php

namespace Rector\Tests\NodeTypeResolver\NodeVisitor\FunctionMethodAndClassNodeVisitor\Fixture;

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
