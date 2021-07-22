<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\Template;

use Rector\Core\Contract\Template\TemplateResolverInterface;
use Stringable;
final class TemplateResolver implements \Rector\Core\Contract\Template\TemplateResolverInterface
{
    /**
     * @var string
     */
    private const TYPE = 'typo3';
    public function __toString() : string
    {
        return self::TYPE;
    }
    public function provide() : string
    {
        return __DIR__ . '/../../templates/rector.php.dist';
    }
    /**
     * @param string $type
     */
    public function supports($type) : bool
    {
        return self::TYPE === $type;
    }
}
