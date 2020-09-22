<?php

declare(strict_types=1);

use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\Interface_;

return new Interface_(new Identifier('InterfaceName'));
