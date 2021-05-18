<?php

namespace RectorPrefix20210518\Helmich\TypoScriptParser\Tests\Unit\Parser\AST;

use RectorPrefix20210518\Helmich\TypoScriptParser\Parser\AST\RootObjectPath;
use RectorPrefix20210518\PHPUnit\Framework\TestCase;
class RootObjectPathTest extends \RectorPrefix20210518\PHPUnit\Framework\TestCase
{
    /** @var RootObjectPath */
    private $path;
    public function setUp() : void
    {
        $this->path = new \RectorPrefix20210518\Helmich\TypoScriptParser\Parser\AST\RootObjectPath();
    }
    public function testAbsoluteNameIsEmpty()
    {
        assertThat($this->path->absoluteName, equalTo(''));
    }
    public function testRelativeNameIsEmpty()
    {
        assertThat($this->path->relativeName, equalTo(''));
    }
    public function testDepthIsZero()
    {
        assertThat($this->path->depth(), equalTo(0));
    }
    public function testParentIsAlsoRoot()
    {
        assertThat($this->path->parent()->depth(), equalTo(0));
        assertThat($this->path->parent()->absoluteName, equalTo(''));
    }
    public function testCanAppendPath()
    {
        $new = $this->path->append('foo');
        assertThat($new->absoluteName, equalTo('foo'));
        assertThat($new->relativeName, equalTo('foo'));
        assertThat($new->depth(), equalTo(1));
    }
}
