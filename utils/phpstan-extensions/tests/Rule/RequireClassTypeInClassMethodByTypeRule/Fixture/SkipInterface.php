<?php


namespace Rector\PHPStanExtensions\Tests\Rule\RequireClassTypeInClassMethodByTypeRule\Fixture;

use Rector\Core\Contract\Rector\PhpRectorInterface;

interface SkipInterface extends PhpRectorInterface
{
    /**
     * @return string[]
     */
    public function getNodeTypes(): array;
}
