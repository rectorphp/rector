<?php

namespace Rector\Tests\Php81\Rector\Property\ReadOnlyPropertyRector\Fixture;

final class SkipConditionallyChangedProperty
{
    public function __construct(private Converter $converter)
    {
        if ($this->converter instanceof CacheableConverter) {
            $this->converter = clone $this->converter;
            $this->converter->reset();
        }
    }
}

?>
