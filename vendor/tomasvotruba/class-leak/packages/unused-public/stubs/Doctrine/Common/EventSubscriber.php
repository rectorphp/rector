<?php

declare (strict_types=1);
namespace RectorPrefix202608\Doctrine\Common;

interface EventSubscriber
{
    /**
     * @return string[]
     */
    public function getSubscribedEvents();
}
