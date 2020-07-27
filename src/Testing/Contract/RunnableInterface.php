<?php

declare(strict_types=1);

namespace Rector\Core\Testing\Contract;

interface RunnableInterface
{
    /**
     * @return mixed
     */
    public function run();
}
