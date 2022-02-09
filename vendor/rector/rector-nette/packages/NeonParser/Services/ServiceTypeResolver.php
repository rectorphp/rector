<?php

declare (strict_types=1);
namespace Rector\Nette\NeonParser\Services;

use RectorPrefix20220209\Nette\Neon\Node;
use RectorPrefix20220209\Nette\Neon\Node\ArrayItemNode;
use RectorPrefix20220209\Nette\Neon\Node\ArrayNode;
use RectorPrefix20220209\Nette\Neon\Node\EntityNode;
final class ServiceTypeResolver
{
    /**
     * @var string
     */
    private const FACTORY_KEYWORD = 'factory';
    /**
     * @var string
     */
    private const CLASS_KEYWORD = 'class';
    /**
     * @return string|null
     */
    public function resolve(\RectorPrefix20220209\Nette\Neon\Node $serviceNode)
    {
        if (!$serviceNode instanceof \RectorPrefix20220209\Nette\Neon\Node\ArrayItemNode) {
            return null;
        }
        if (!$serviceNode->value instanceof \RectorPrefix20220209\Nette\Neon\Node\ArrayNode) {
            return null;
        }
        foreach ($serviceNode->value->items as $serviceConfigurationItem) {
            if ($serviceConfigurationItem->key === null) {
                continue;
            }
            if ($serviceConfigurationItem->key->toString() === self::FACTORY_KEYWORD) {
                if ($serviceConfigurationItem->value instanceof \RectorPrefix20220209\Nette\Neon\Node\EntityNode) {
                    return $serviceConfigurationItem->value->value->toString();
                }
                return $serviceConfigurationItem->value->toString();
            }
            if ($serviceConfigurationItem->key->toString() === self::CLASS_KEYWORD) {
                if ($serviceConfigurationItem->value instanceof \RectorPrefix20220209\Nette\Neon\Node\EntityNode) {
                    return $serviceConfigurationItem->value->value->toString();
                }
                return $serviceConfigurationItem->value->toString();
            }
        }
        return null;
    }
}
