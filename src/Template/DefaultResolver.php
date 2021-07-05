<?php

declare (strict_types=1);
namespace Rector\Core\Template;

use Rector\Core\Contract\Template\TemplateResolverInterface;
final class DefaultResolver implements \Rector\Core\Contract\Template\TemplateResolverInterface
{
    /**
     * @var string
     */
    public const TYPE = 'default';
    public function provide() : string
    {
        return __DIR__ . '/../../templates/rector.php.dist';
    }
    /**
     * @param string $type
     */
    public function supports($type) : bool
    {
        return $type === self::TYPE;
    }
    public function getType() : string
    {
        return self::TYPE;
    }
}
