<?php

namespace Rector\Tests\Php80\Rector\FunctionLike\UnionTypesRector\Fixture;

use DateTime;

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

namespace Rector\Tests\Php80\Rector\FunctionLike\UnionTypesRector\Fixture;

use DateTime;

final class ObjectAndSpecificClass
{
    /**
     * @param DateTime $message
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
