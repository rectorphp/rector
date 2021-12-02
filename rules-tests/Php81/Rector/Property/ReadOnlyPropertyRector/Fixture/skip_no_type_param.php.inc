<?php

namespace Rector\Tests\Php81\Rector\Property\ReadOnlyPropertyRector\Fixture;

final class SkipNoTypeParam
{
    /**
     * @param mixed $data
     */
    public function __construct(
        private $data
    ) {
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }
}
