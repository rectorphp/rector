<?php

declare (strict_types=1);
namespace RectorPrefix20211020\Idiosyncratic\EditorConfig\Exception;

use DomainException;
use function sprintf;
class InvalidValue extends \DomainException
{
    public function __construct(string $declaration, string $value)
    {
        parent::__construct(\sprintf('%s is not a valid value for \'%s\'', $declaration, $value));
    }
}
