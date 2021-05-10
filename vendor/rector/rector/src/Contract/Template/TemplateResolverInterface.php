<?php

declare (strict_types=1);
namespace Rector\Core\Contract\Template;

use Stringable;
interface TemplateResolverInterface extends \Stringable
{
    public function provide() : string;
    public function supports(string $type) : bool;
}
