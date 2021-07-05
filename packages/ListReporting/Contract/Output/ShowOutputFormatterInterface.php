<?php

declare (strict_types=1);
namespace Rector\ListReporting\Contract\Output;

use Rector\Core\Contract\Rector\RectorInterface;
interface ShowOutputFormatterInterface
{
    /**
     * @param RectorInterface[] $rectors
     */
    public function list($rectors) : void;
    public function getName() : string;
}
