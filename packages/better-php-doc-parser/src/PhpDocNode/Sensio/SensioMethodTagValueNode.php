<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocNode\Sensio;

use Rector\BetterPhpDocParser\Contract\PhpDocNode\ShortNameAwareTagInterface;
use Rector\BetterPhpDocParser\PhpDocNode\AbstractTagValueNode;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

final class SensioMethodTagValueNode extends AbstractTagValueNode implements ShortNameAwareTagInterface
{
    /**
     * @var string
     */
    public const CLASS_NAME = Method::class;

    /**
     * @var string[]
     */
    private $methods = [];

    /**
     * @param string[] $methods
     */
    public function __construct(array $methods = [])
    {
        $this->methods = $methods;
    }

    public function __toString(): string
    {
        return '(' . $this->printArrayItem($this->methods) . ')';
    }

    /**
     * @return string[]
     */
    public function getMethods(): array
    {
        return $this->methods;
    }

    public function getShortName(): string
    {
        return '@Method';
    }
}
