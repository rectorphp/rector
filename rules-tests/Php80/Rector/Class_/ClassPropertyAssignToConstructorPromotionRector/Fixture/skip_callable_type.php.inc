<?php

namespace Rector\Tests\Php80\Rector\Class_\ClassPropertyAssignToConstructorPromotionRector\Fixture;

final class SkipCallableType
{
    /** @var callable */
    private $fallback;

    /**
     * @param mixed[] $cache
     */
    public function __construct(callable $fallback, private array $cache = [])
    {
        $this->fallback = $fallback;
    }
}

?>
