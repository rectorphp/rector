<?php

declare(strict_types=1);

namespace Rector\Naming\ValueObject;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassLike;

interface RenameValueObjectInterface
{
    public function getNode(): Node;

    public function getCurrentName(): string;

    public function getExpectedName(): string;

    public function getClassLike(): ClassLike;
}
