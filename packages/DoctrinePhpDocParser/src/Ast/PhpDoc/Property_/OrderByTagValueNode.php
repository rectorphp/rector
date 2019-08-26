<?php declare(strict_types=1);

namespace Rector\DoctrinePhpDocParser\Ast\PhpDoc\Property_;

use Nette\Utils\Json;
use Nette\Utils\Strings;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagValueNode;
use Rector\BetterPhpDocParser\Attributes\Attribute\AttributeTrait;
use Rector\BetterPhpDocParser\Attributes\Contract\Ast\AttributeAwareNodeInterface;
use Rector\DoctrinePhpDocParser\Contract\Ast\PhpDoc\DoctrineTagNodeInterface;

final class OrderByTagValueNode implements PhpDocTagValueNode, AttributeAwareNodeInterface, DoctrineTagNodeInterface
{
    use AttributeTrait;

    /**
     * @var mixed[]
     */
    private $value = [];

    /**
     * @param string[] $value
     */
    public function __construct(array $value)
    {
        $this->value = $value;
    }

    public function __toString(): string
    {
        $json = Json::encode($this->value);
        $json = Strings::replace($json, '#,#', ', ');
        $json = Strings::replace($json, '#:#', ' = ');

        return '(' . $json . ')';
    }
}
