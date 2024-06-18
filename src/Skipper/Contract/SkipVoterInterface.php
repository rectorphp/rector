<?php

declare (strict_types=1);
namespace Rector\Skipper\Contract;

/**
 * @api implement with custom extension if needed
 */
interface SkipVoterInterface
{
    /**
     * @param string|object $element
     */
    public function match($element) : bool;
    /**
     * @param string|object $element
     */
    public function shouldSkip($element, string $filePath) : bool;
}
