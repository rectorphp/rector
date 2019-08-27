<?php declare(strict_types=1);

namespace Rector\DoctrinePhpDocParser\Ast\PhpDoc\Property_;

use Rector\DoctrinePhpDocParser\Ast\PhpDoc\AbstractDoctrineTagValueNode;

final class GeneratedValueTagValueNode extends AbstractDoctrineTagValueNode
{
    /**
     * @var string
     */
    public const SHORT_NAME = '@ORM\GeneratedValue';

    /**
     * @var string
     */
    private $strategy;

    public function __construct(string $strategy)
    {
        $this->strategy = $strategy;
    }

    public function __toString(): string
    {
        return sprintf('(stragety="%s")', $this->strategy);
    }
}
