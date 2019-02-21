<?php declare(strict_types=1);


final class SomeQuality
{
    public function run()
    {
        if (null !== $this->orderItem) {
            return $this->orderItem;
        }
        if (null !== $this->orderItemUnit) {
            return $this->orderItemUnit;
        }
        return null;
    }
}
