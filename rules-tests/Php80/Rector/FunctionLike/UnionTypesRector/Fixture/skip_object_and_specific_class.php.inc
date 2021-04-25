<?php

namespace Rector\Php80\Tests\Rector\FunctionLike\UnionTypesRector\Fixture;

final class SkipObjectAndSpecificClass
{
    /**
     * @param object|DateTime $message
     */
    private function getMessage($message)
    {
        if ($message instanceof \DateTime) {
            // do something special
        }

        return $message;
    }
}
?>

