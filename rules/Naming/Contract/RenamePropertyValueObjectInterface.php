<?php

declare (strict_types=1);
namespace Rector\Naming\Contract;

use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\Stmt\PropertyProperty;
interface RenamePropertyValueObjectInterface extends \Rector\Naming\Contract\RenameValueObjectInterface
{
    public function getClassLike() : ClassLike;
    public function getClassLikeName() : string;
    public function getProperty() : Property;
    public function getPropertyProperty() : PropertyProperty;
}
