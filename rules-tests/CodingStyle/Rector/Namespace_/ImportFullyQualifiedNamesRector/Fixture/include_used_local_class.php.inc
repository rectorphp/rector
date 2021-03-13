<?php

declare(strict_types=1);

namespace Rector\Tests\CodingStyle\Rector\Namespace_\ImportFullyQualifiedNamesRector\Fixture;

final class IncludeUsedLocalClass
{
    /**
     * @var \Rector\Tests\CodingStyle\Rector\Namespace_\ImportFullyQualifiedNamesRector\Fixture\SharedShortName
     */
    private $join;

    public function __construct(SharedShortName $join)
    {
    }

    public function getJoin(): SharedShortName
    {
        return $this->join;
    }
}

?>
-----
<?php

declare(strict_types=1);

namespace Rector\Tests\CodingStyle\Rector\Namespace_\ImportFullyQualifiedNamesRector\Fixture;

final class IncludeUsedLocalClass
{
    /**
     * @var SharedShortName
     */
    private $join;

    public function __construct(SharedShortName $join)
    {
    }

    public function getJoin(): SharedShortName
    {
        return $this->join;
    }
}

?>
