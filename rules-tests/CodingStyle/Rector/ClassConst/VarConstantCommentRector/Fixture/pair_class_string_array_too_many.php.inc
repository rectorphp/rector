<?php

namespace Rector\Tests\CodingStyle\Rector\ClassConst\VarConstantCommentRector\Fixture;

class A { }
class Aa { }
class B { }
class Bb { }
class C { }
class Cc { }
class D { }
class Dd { }

class PairClassStringArrayTooMany
{
    private const CLASSES = [
        A::class => Aa::class,
        B::class => Bb::class,
        C::class => Cc::class,
        D::class => Dd::class,
    ];
}

?>
-----
<?php

namespace Rector\Tests\CodingStyle\Rector\ClassConst\VarConstantCommentRector\Fixture;

class A { }
class Aa { }
class B { }
class Bb { }
class C { }
class Cc { }
class D { }
class Dd { }

class PairClassStringArrayTooMany
{
    /**
     * @var array<string, class-string>
     */
    private const CLASSES = [
        A::class => Aa::class,
        B::class => Bb::class,
        C::class => Cc::class,
        D::class => Dd::class,
    ];
}

?>
