<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\Naming\Contract;

use RectorPrefix20220606\PhpParser\Node\Stmt\ClassLike;
use RectorPrefix20220606\PhpParser\Node\Stmt\Property;
use RectorPrefix20220606\PhpParser\Node\Stmt\PropertyProperty;
interface RenamePropertyValueObjectInterface extends RenameValueObjectInterface
{
    public function getClassLike() : ClassLike;
    public function getClassLikeName() : string;
    public function getProperty() : Property;
    public function getPropertyProperty() : PropertyProperty;
}
