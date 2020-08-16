<?php

namespace Rector\Renaming\Tests\Rector\ClassMethod\RenameAnnotationRector\Fixture;

/*
 * This definition fails only when is extending \PHPUnit\Framework\TestCase
 */
final class FixtureRenameWithMethodWithCommentTest extends \PHPUnit\Framework\TestCase
{
    /*
     * this is a comment, not a phpdoc
     */
    public function test()
    {
    }
}

?>
