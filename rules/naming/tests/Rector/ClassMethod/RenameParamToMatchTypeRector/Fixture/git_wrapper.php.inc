<?php

namespace Rector\Naming\Tests\Rector\ClassMethod\RenameParamToMatchTypeRector\Fixture;

use Rector\Naming\Tests\Rector\ClassMethod\RenameParamToMatchTypeRector\Source\GitWrapper;

class GitWrapperChange
{
    /**
     * @var \Rector\Naming\Tests\Rector\ClassMethod\RenameParamToMatchTypeRector\Source\GitWrapper
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

namespace Rector\Naming\Tests\Rector\ClassMethod\RenameParamToMatchTypeRector\Fixture;

use Rector\Naming\Tests\Rector\ClassMethod\RenameParamToMatchTypeRector\Source\GitWrapper;

class GitWrapperChange
{
    /**
     * @var \Rector\Naming\Tests\Rector\ClassMethod\RenameParamToMatchTypeRector\Source\GitWrapper
     */
    protected $wrapper;

    public function __construct(GitWrapper $gitWrapper)
    {
        $this->wrapper = $gitWrapper;
    }
}

?>
