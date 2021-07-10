<?php

namespace Rector\Tests\Defluent\Rector\ClassMethod\ReturnThisRemoveRector\Fixture;

class SkipMagicCall
{
    public $author;
    public $title;

    /**
     * @return $this
     */
    public function __call(string $name, array $args)
    {
        if (! method_exists($this, $name) && strpos($name, 'set') === 0) {
            $this->{strtolower(substr($name, 3))} = $args[0];
        }

        return $this;
    }
}

$obj = new SkipMagicCall();
$obj->setAuthor('author')
    ->setTitle('title');

?>
