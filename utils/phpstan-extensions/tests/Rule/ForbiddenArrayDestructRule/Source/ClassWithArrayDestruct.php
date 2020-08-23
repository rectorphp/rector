<?php

declare(strict_types=1);

namespace Rector\PHPStanExtensions\Tests\Rule\ForbiddenArrayDestructRule\Source;

final class ClassWithArrayDestruct
{
    public function run()
    {
        [$one, $two] = $this->getResult();
    }

    public function getResult()
    {
        return [1, 2];
    }
}
