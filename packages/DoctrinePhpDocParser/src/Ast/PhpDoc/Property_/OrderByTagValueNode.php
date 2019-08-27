<?php declare(strict_types=1);

namespace Rector\DoctrinePhpDocParser\Ast\PhpDoc\Property_;

use Nette\Utils\Json;
use Nette\Utils\Strings;
use Rector\DoctrinePhpDocParser\Ast\PhpDoc\AbstractDoctrineTagValueNode;

final class OrderByTagValueNode extends AbstractDoctrineTagValueNode
{
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
