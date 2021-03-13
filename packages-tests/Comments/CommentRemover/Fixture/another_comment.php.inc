<?php

namespace Rector\Comments\Tests\CommentRemover\Fixture;

$values = new class
{
    public function run($value)
    {
        // else
        switch ($value) {
            case 'key':
                // something
                return 'https://some_very_long_link.cz';  /* here too */
                break;
        }
    }
};


?>
-----
namespace Rector\Comments\Tests\CommentRemover\Fixture;

$values = new class
{
    public function run($value)
    {
        switch ($value) {
            case 'key':
                return 'https://some_very_long_link.cz';
                break;
        }
    }
};
