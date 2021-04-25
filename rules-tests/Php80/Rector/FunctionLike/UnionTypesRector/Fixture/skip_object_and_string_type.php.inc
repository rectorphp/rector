<?php

namespace Rector\Tests\Php80\Rector\FunctionLike\UnionTypesRector\Fixture;

final class ObjectAndStringType
{
    /**
     * @param object|string $message
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
