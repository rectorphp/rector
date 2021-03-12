<?php

namespace Rector\Tests\Naming\Rector\ClassMethod\RenameParamToMatchTypeRector\Fixture;

use Rector\Tests\Naming\Rector\ClassMethod\RenameParamToMatchTypeRector\Source\GitWrapper;

class GitWrapperChange
{
    /**
     * @var \Rector\Tests\Naming\Rector\ClassMethod\RenameParamToMatchTypeRector\Source\GitWrapper
     */
    protected $wrapper;

    public function __construct(GitWrapper $wrapper)
    {
        $this->wrapper = $wrapper;
    }
}

?>
-----
<?php

namespace Rector\Tests\Naming\Rector\ClassMethod\RenameParamToMatchTypeRector\Fixture;

use Rector\Tests\Naming\Rector\ClassMethod\RenameParamToMatchTypeRector\Source\GitWrapper;

class GitWrapperChange
{
    /**
     * @var \Rector\Tests\Naming\Rector\ClassMethod\RenameParamToMatchTypeRector\Source\GitWrapper
     */
    protected $wrapper;

    public function __construct(GitWrapper $gitWrapper)
    {
        $this->wrapper = $gitWrapper;
    }
}

?>
