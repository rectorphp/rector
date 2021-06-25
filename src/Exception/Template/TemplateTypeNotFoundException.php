<?php

declare (strict_types=1);
namespace Rector\Core\Exception\Template;

use RuntimeException;
final class TemplateTypeNotFoundException extends \RuntimeException
{
    /**
     * @param string[] $availableTypes
     */
    public function __construct(string $type, array $availableTypes)
    {
        $availableTypesAsString = \implode("', ", $availableTypes);
        $message = \sprintf('No template found for type "%s". Possible values are "%s"', $type, $availableTypesAsString);
        parent::__construct($message);
    }
}
