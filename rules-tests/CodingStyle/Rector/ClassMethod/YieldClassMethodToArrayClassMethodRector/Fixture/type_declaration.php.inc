<?php

namespace Rector\Tests\CodingStyle\Rector\ClassMethod\YieldClassMethodToArrayClassMethodRector\Fixture;

use Rector\Tests\CodingStyle\Rector\ClassMethod\YieldClassMethodToArrayClassMethodRector\Source\EventSubscriberInterface;

class TypeDeclaration implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): \Iterator
    {
        yield 'event' => 'callback';
    }
}

?>
-----
<?php

namespace Rector\Tests\CodingStyle\Rector\ClassMethod\YieldClassMethodToArrayClassMethodRector\Fixture;

use Rector\Tests\CodingStyle\Rector\ClassMethod\YieldClassMethodToArrayClassMethodRector\Source\EventSubscriberInterface;

class TypeDeclaration implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return ['event' => 'callback'];
    }
}

?>
