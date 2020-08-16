<?php

declare(strict_types=1);

final class DelegatingPhp4ConstructorClassAgain
{
    /**
     * Constructor
     */
    public function __construct($bar = 1, $baz = null)
    {
        var_dump(1);
    }

    /**
     * PHP-4 Constructor
     */
    function DelegatingPhp4ConstructorClassAgain($bar = 1, $baz = null)
    {
        // Call PHP5!
        $this->__construct($bar = 1, $baz = null);
    }
}

?>
-----
<?php

declare(strict_types=1);

final class DelegatingPhp4ConstructorClassAgain
{
    /**
     * Constructor
     */
    public function __construct($bar = 1, $baz = null)
    {
        var_dump(1);
    }
}

?>
