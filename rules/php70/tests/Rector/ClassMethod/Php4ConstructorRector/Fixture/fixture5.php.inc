<?php

class FooParEnt
{
}

class Php4ConstructorWithParent extends FooParEnt
{
    /**
     * Constructor
     */
    function Php4ConstructorWithParent($bar)
    {
        parent::FooPaRent(1);
        var_dump(9);
    }
}

class Php4ConstructorWithParentOnly extends FooParent
{
    /**
     * Constructor
     */
    function __construcT($bar)
    {
        parent::FooParenT(1);
        var_dump(9);
    }
}

class Php4ConstructorWithParentDouble extends FooParent
{
    /**
     * Constructor
     */
    function Foo($bar)
    {
        FooParent::FooParent(1);
        var_dump(9);
    }
}

?>
-----
<?php

class FooParEnt
{
}

class Php4ConstructorWithParent extends FooParEnt
{
    /**
     * Constructor
     */
    function __construct($bar)
    {
        parent::__construct(1);
        var_dump(9);
    }
}

class Php4ConstructorWithParentOnly extends FooParent
{
    /**
     * Constructor
     */
    function __construcT($bar)
    {
        parent::__construct(1);
        var_dump(9);
    }
}

class Php4ConstructorWithParentDouble extends FooParent
{
    /**
     * Constructor
     */
    function Foo($bar)
    {
        parent::__construct(1);
        var_dump(9);
    }
}

?>
