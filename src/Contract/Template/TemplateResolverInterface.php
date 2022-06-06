<?php

declare (strict_types=1);
namespace Rector\Core\Contract\Template;

/**
 * @deprecated This know-how should be mentioned in framework-specific documentation of the package instead.
 */
interface TemplateResolverInterface
{
    public function provide() : string;
    public function supports(string $type) : bool;
}
