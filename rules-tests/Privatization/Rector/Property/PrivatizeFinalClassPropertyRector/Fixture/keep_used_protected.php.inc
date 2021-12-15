<?php

namespace Rector\Tests\Privatization\Rector\Property\PrivatizeFinalClassPropertyRector\Fixture;

abstract class AbstractKeepUsedProtected {
    protected $abstractOptions = [
        'options' => [],
    ];

    public function __construct()
    {
        if (isset($this->options)) {
            $this->abstractOptions['options'] = $this->options;
        }
    }
}

final class KeepUsedProtected extends AbstractKeepUsedProtected
{
    protected $options = [
        'someSetting' => true
    ];
}
?>
