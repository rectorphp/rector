<?php declare(strict_types=1);

namespace Rector\Contract\Configuration\Rector;

interface ArgumentRecipeInterface
{
    public function getClass(): string;

    public function getMethod(): string;
}
