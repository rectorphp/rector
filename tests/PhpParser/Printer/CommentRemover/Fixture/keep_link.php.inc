<?php

namespace Rector\Core\Tests\PhpParser\Printer\CommentRemover\Fixture;

final class KeepLink
{
    public function run($value)
    {
        switch ($value) {
            case 'key':
                return 'https://some_very_long_link.cz';
                break;
        }
    }
}
