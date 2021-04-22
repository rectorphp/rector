<?php

namespace Rector\Php80\Tests\Rector\FunctionLike\UnionTypesRector\Fixture;

final class ObjectAndSpecificClass
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
-----
<?php

namespace Rector\Php80\Tests\Rector\FunctionLike\UnionTypesRector\Fixture;

final class ObjectAndSpecificClass
{
    /**
     * @param object|DateTime $message
     */
    private function getMessage(object $message)
    {
        if ($message instanceof \DateTime) {
            // do something special
        }
        
        return $message;
    }
}
?>
