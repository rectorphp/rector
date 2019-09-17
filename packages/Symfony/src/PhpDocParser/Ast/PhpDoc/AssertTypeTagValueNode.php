<?php declare(strict_types=1);

namespace Rector\Symfony\PhpDocParser\Ast\PhpDoc;

use Symfony\Component\Validator\Constraints\Type;

final class AssertTypeTagValueNode extends AbstractConstraintTagValueNode
{
    /**
     * @var string
     */
    public const SHORT_NAME = '@Assert\Type';

    /**
     * @var string
     */
    public const CLASS_NAME = Type::class;

    /**
     * @var string
     */
    private $type;

    public function __construct(string $type)
    {
        $this->type = $type;
    }

    public function __toString(): string
    {
        return '("' . $this->type . '")';
    }
}
