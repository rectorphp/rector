<?php

namespace Rector\CodingStyle\Tests\Rector\Throw_\AnnotateThrowablesRector\Fixture;

function throwCustomExceptionInFunctionWithoutDockblock()
{
    throw new \Rector\CodingStyle\Tests\Rector\Throw_\AnnotateThrowablesRector\Source\TheException();
}

?>
-----
<?php

namespace Rector\CodingStyle\Tests\Rector\Throw_\AnnotateThrowablesRector\Fixture;

/**
 * @throws \Rector\CodingStyle\Tests\Rector\Throw_\AnnotateThrowablesRector\Source\TheException
 */
function throwCustomExceptionInFunctionWithoutDockblock()
{
    throw new \Rector\CodingStyle\Tests\Rector\Throw_\AnnotateThrowablesRector\Source\TheException();
}

?>
