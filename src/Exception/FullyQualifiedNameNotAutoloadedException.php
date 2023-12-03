<?php

declare (strict_types=1);
namespace Rector\Core\Exception;

use PhpParser\Node\Name;
use RuntimeException;
final class FullyQualifiedNameNotAutoloadedException extends RuntimeException
{
    /**
     * @var \PhpParser\Node\Name
     */
    protected $name;
    public function __construct(Name $name)
    {
        $this->name = $name;
        parent::__construct(\sprintf('%s was not autoloaded', $name->toString()));
    }
}
