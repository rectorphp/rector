<?php declare(strict_types=1);

namespace Rector\DoctrinePhpDocParser\Ast\PhpDoc\Class_;

use Rector\DoctrinePhpDocParser\Ast\PhpDoc\AbstractDoctrineTagValueNode;

final class InheritanceTypeTagValueNode extends AbstractDoctrineTagValueNode
{
    /**
     * @var string
     */
    public const SHORT_NAME = '@ORM\InheritanceType';

    /**
     * @var string|null
     */
    private $value;

    public function __construct(?string $value)
    {
        $this->value = $value;
    }

    public function __toString(): string
    {
        if ($this->value === null) {
            return '';
        }

        return '("' . $this->value . '")';
    }
}
