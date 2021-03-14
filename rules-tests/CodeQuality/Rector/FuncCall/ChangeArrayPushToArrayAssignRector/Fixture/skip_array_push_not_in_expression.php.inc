<?php

namespace Rector\Tests\CodeQuality\Rector\FuncCall\ChangeArrayPushToArrayAssignRector\Fixture;

final class SkipArrayPushNotInExpression
{
    public function run()
    {
        $tagstack  = array();
        $tag = 'a';
        $stacksize = array_push( $tagstack, $tag );
    }
}
