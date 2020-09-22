<?php

namespace Rector\Naming\Tests\Rector\Class_\RenamePropertyToMatchTypeRector\Fixture;

use Rector\Naming\Tests\Rector\Class_\RenamePropertyToMatchTypeRector\Source\GitWrapper;

class GitWrapperChange
{
    /**
     * @var \Rector\Naming\Tests\Rector\Class_\RenamePropertyToMatchTypeRector\Source\GitWrapper
     */
    private $wrapper;

    public function __construct(GitWrapper $wrapper)
    {
        $this->wrapper = $wrapper;
    }
}

?>
-----
<?php

namespace Rector\Naming\Tests\Rector\Class_\RenamePropertyToMatchTypeRector\Fixture;

use Rector\Naming\Tests\Rector\Class_\RenamePropertyToMatchTypeRector\Source\GitWrapper;

class GitWrapperChange
{
    /**
     * @var \Rector\Naming\Tests\Rector\Class_\RenamePropertyToMatchTypeRector\Source\GitWrapper
     */
    private $gitWrapper;

    public function __construct(GitWrapper $wrapper)
    {
        $this->gitWrapper = $wrapper;
    }
}

?>
