<?php

declare (strict_types=1);
namespace Rector\PhpParser\NodeTraverser;

use PhpParser\NodeTraverser;
use Rector\PHPStan\NodeVisitor\ReIndexNodeAttributeVisitor;
final class ReIndexNodeAttributesTraverser extends NodeTraverser
{
    public function __construct()
    {
        parent::__construct(new ReIndexNodeAttributeVisitor());
    }
}
