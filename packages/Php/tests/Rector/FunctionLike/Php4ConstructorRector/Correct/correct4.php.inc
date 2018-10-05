<?php declare(strict_types=1);

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
