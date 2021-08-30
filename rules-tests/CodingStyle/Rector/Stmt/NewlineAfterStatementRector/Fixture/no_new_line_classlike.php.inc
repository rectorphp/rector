<?php

namespace A
{
    class C
    {
    }
    class D
    {
    }
}
namespace B
{
    interface E
    {
    }
    trait F
    {
    }
    class G implements E
    {
        use F;
    }
}

?>
-----
<?php

namespace A
{
    class C
    {
    }

    class D
    {
    }
}

namespace B
{
    interface E
    {
    }

    trait F
    {
    }

    class G implements E
    {
        use F;
    }
}

?>
