<?php

namespace Rector\DeadCode\Tests\Rector\PropertyProperty\RemoveNullPropertyInitializationRector\Fixture;

class Fixture
{
    private $lowerCase = null;

    private $titleCaseNullConstant = Null;

    private $upperCaseNullConstant = NULL;

    private $notANull = 'null';

    /**
     * @var null
     */
    private $nullInPhpDocs;

    private $notANullWithDoubleQuotes = "null";

}

?>
-----
<?php

namespace Rector\DeadCode\Tests\Rector\PropertyProperty\RemoveNullPropertyInitializationRector\Fixture;

class Fixture
{
    private $lowerCase;

    private $titleCaseNullConstant;

    private $upperCaseNullConstant;

    private $notANull = 'null';

    /**
     * @var null
     */
    private $nullInPhpDocs;

    private $notANullWithDoubleQuotes = "null";

}

?>
