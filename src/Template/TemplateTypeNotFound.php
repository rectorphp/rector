<?php
declare(strict_types=1);

namespace Rector\Core\Template;

use RuntimeException;

final class TemplateTypeNotFound extends RuntimeException
{
    public static function typeNotFound(string $type, string $availableTypes): self
    {
        $message = sprintf('No template found for type %s. Possible values are %s', $type, $availableTypes);

        return new self($message);
    }
}
