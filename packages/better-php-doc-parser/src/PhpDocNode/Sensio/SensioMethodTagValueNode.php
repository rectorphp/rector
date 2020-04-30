<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocNode\Sensio;

use Rector\BetterPhpDocParser\Contract\PhpDocNode\ShortNameAwareTagInterface;
use Rector\BetterPhpDocParser\PhpDocNode\AbstractTagValueNode;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

final class SensioMethodTagValueNode extends AbstractTagValueNode implements ShortNameAwareTagInterface
{
    public function __construct(Method $method, string $content)
    {
        $this->items = get_object_vars($method);
        $this->resolveOriginalContentSpacingAndOrder($content, 'methods');
    }

    /**
     * @return string[]
     */
    public function getMethods(): array
    {
        return $this->items['methods'];
    }

    public function getShortName(): string
    {
        return '@Method';
    }
}
