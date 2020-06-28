<?php

declare(strict_types=1);

namespace Rector\Decouple\Tests\Rector\DecoupleClassMethodToOwnClassRector\Fixture;

use Rector\Decouple\Tests\Rector\DecoupleClassMethodToOwnClassRector\Source\EventManager;

final class WithPropertyDependency
{
    /**
     * @var EventManager
     */
    private $eventManager;

    public function __construct(EventManager $eventManager)
    {
        $this->eventManager = $eventManager;
    }

    public function usingProperty()
    {
        return $this->eventManager->runEvent();
    }
}

?>
-----
<?php

declare(strict_types=1);

namespace Rector\Decouple\Tests\Rector\DecoupleClassMethodToOwnClassRector\Fixture;

use Rector\Decouple\Tests\Rector\DecoupleClassMethodToOwnClassRector\Source\EventManager;

final class WithPropertyDependency
{
    /**
     * @var EventManager
     */
    private $eventManager;

    public function __construct(EventManager $eventManager)
    {
        $this->eventManager = $eventManager;
    }
}

?>
