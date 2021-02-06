<?php

namespace Rector\CodeQualityStrict\Tests\Rector\Variable\MoveVariableDeclarationNearReferenceRector\Fixture;

final class SkipObGetContents
{
    public function run()
    {
        ob_start();
        $git->status();
        $contents = ob_get_contents();
        ob_end_clean();
      
        return $contents;
    }
}
