<?php declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocNode\Sensio;

use Rector\BetterPhpDocParser\PhpDocParser\Ast\PhpDoc\AbstractTagValueNode;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

final class SensioMethodTagValueNode extends AbstractTagValueNode
{
    /**
     * @var string
     */
    public const SHORT_NAME = '@Method';

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
}
