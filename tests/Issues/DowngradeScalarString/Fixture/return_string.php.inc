<?php

namespace Rector\Core\Tests\Issues\DowngradeScalarString\Fixture;

abstract class AbstractReturnString
{
    public function __toString(): string
    {
        return 'value';
    }
}

class ReturnString extends AbstractReturnString
{
    public function __toString()
    {
        return 'value';
    }
}

?>
-----
<?php

namespace Rector\Core\Tests\Issues\DowngradeScalarString\Fixture;

abstract class AbstractReturnString
{
    /**
     * @return string
     */
    public function __toString()
    {
        return 'value';
    }
}

class ReturnString extends AbstractReturnString
{
    /**
     * @return string
     */
    public function __toString()
    {
        return 'value';
    }
}

?>
