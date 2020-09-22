<?php

use PhpParser\Node\Identifier;
use PhpParser\Node\UnionType;

$unionedTypes = [new Identifier('string'), new Identifier('int')];

return new UnionType($unionedTypes);
