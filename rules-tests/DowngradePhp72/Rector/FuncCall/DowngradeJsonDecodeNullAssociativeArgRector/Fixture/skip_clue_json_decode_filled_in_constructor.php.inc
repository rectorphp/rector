<?php

namespace Rector\Tests\DowngradePhp72\Rector\FuncCall\DowngradeJsonDecodeNullAssociativeArgRector\Fixture;

final class SkipClueJsonDecodeFilledInConstructor
{
    private $assoc;
    private $depth;

    /**
     * @param bool $assoc
     */
    public function __construct($assoc = false)
    {
        $this->assoc = $assoc;
    }

    public function run($data)
    {
        $data = \json_decode($data, $this->assoc, $this->depth);
    }
}
