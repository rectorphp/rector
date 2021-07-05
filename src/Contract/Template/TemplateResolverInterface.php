<?php

declare (strict_types=1);
namespace Rector\Core\Contract\Template;

interface TemplateResolverInterface
{
    // public function getType(): string;
    public function provide() : string;
    /**
     * @param string $type
     */
    public function supports($type) : bool;
}
