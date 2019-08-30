<?php declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocParser\Ast\PhpDoc;

use Nette\Utils\Json;
use Nette\Utils\Strings;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagValueNode;
use Rector\BetterPhpDocParser\Attributes\Attribute\AttributeTrait;
use Rector\BetterPhpDocParser\Attributes\Contract\Ast\AttributeAwareNodeInterface;

abstract class AbstractTagValueNode implements AttributeAwareNodeInterface, PhpDocTagValueNode
{
    use AttributeTrait;

    /**
     * @param mixed[] $item
     */
    protected function printArrayItem(array $item, string $key): string
    {
        $json = Json::encode($item);
        $json = Strings::replace($json, '#,#', ', ');
        $json = Strings::replace($json, '#\[(.*?)\]#', '{$1}');

        return sprintf('%s=%s', $key, $json);
    }
}
