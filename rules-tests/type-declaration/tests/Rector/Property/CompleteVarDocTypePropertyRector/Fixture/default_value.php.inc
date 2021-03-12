<?php

namespace Rector\TypeDeclaration\Tests\Rector\Property\CompleteVarDocTypePropertyRector\Fixture;

final class DefaultValue
{
    private $number = 5;

    private $maybe = false;

    private $name = 'John';

    private $longName = 'Elton' . 'John';
}

?>
-----
<?php

namespace Rector\TypeDeclaration\Tests\Rector\Property\CompleteVarDocTypePropertyRector\Fixture;

final class DefaultValue
{
    /**
     * @var int
     */
    private $number = 5;

    /**
     * @var bool
     */
    private $maybe = false;

    /**
     * @var string
     */
    private $name = 'John';

    /**
     * @var string
     */
    private $longName = 'Elton' . 'John';
}

?>
