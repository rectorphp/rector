<?php

namespace Rector\DeadCode\Tests\Rector\FunctionLike\RemoveDuplicatedIfReturnRector\Fixture;

class SkipWithDifferntValue
{
    public function run($value)
    {
        // is yml/yaml file
        if (Strings::match($possibleFileNodeAsString, '#\.(yml|yaml)(\'|")$#')) {
            return true;
        }

        // is probably a file variable
        if (Strings::match($possibleFileNodeAsString, '#\File$#')) {
            return true;
        }
    }
}
