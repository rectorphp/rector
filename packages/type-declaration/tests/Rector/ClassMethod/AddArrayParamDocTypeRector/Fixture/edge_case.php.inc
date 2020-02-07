<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddArrayParamDocTypeRector\Fixture;

use Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddArrayParamDocTypeRector\Source\MeetingRoom;

class EdgeCase
{
    /**
     * @var MeetingRoom[]
     */
    private $amenities;

    public function __construct(array $amenities)
    {
        $this->amenities = $amenities;
    }
}

?>
-----
<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddArrayParamDocTypeRector\Fixture;

use Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddArrayParamDocTypeRector\Source\MeetingRoom;

class EdgeCase
{
    /**
     * @var MeetingRoom[]
     */
    private $amenities;

    /**
     * @param \Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddArrayParamDocTypeRector\Source\MeetingRoom[] $amenities
     */
    public function __construct(array $amenities)
    {
        $this->amenities = $amenities;
    }
}

?>
