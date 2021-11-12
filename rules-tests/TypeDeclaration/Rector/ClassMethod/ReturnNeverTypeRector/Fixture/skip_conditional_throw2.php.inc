<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\ReturnNeverTypeRector\Fixture;

function assertEmptyOutputBuffer($messagePrefix)
{
    if (Rocket::isAppMode('development')) {
        if (ob_get_length() > 0) {
            throw new Exception($messagePrefix.'Unexpected start of output:'.print_r(ob_get_contents(), true));
        }
    }
}
