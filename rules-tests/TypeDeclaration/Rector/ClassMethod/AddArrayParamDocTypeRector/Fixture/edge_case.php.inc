<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\AddArrayParamDocTypeRector\Fixture;

use Rector\Tests\TypeDeclaration\Rector\ClassMethod\AddArrayParamDocTypeRector\Source\MeetingRoom;

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

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\AddArrayParamDocTypeRector\Fixture;

use Rector\Tests\TypeDeclaration\Rector\ClassMethod\AddArrayParamDocTypeRector\Source\MeetingRoom;

class EdgeCase
{
    /**
     * @var MeetingRoom[]
     */
    private $amenities;

    /**
     * @param \Rector\Tests\TypeDeclaration\Rector\ClassMethod\AddArrayParamDocTypeRector\Source\MeetingRoom[] $amenities
     */
    public function __construct(array $amenities)
    {
        $this->amenities = $amenities;
    }
}

?>
