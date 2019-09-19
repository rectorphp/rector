<?php declare(strict_types=1);

namespace Rector\Symfony\PhpDocParser\Ast\PhpDoc;

use JMS\Serializer\Annotation\Type;
use Nette\Utils\Strings;
use Rector\BetterPhpDocParser\PhpDocParser\Ast\PhpDoc\AbstractTagValueNode;

final class SerializerTypeTagValueNode extends AbstractTagValueNode
{
    /**
     * @var string
     */
    public const SHORT_NAME = '@Serializer\Type';

    /**
     * @var string
     */
    public const CLASS_NAME = Type::class;

    /**
     * @var string
     */
    private $name;

    public function __construct(string $name, ?string $annotationContent)
    {
        $this->name = $name;
        $this->resolveOriginalContentSpacingAndOrder($annotationContent);
    }

    public function __toString(): string
    {
        return sprintf('("%s")', $this->name);
    }

    public function changeName(string $newName): void
    {
        $this->name = $newName;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function replaceName(string $oldName, string $newName): bool
    {
        $oldNamePattern = '#\b' . preg_quote($oldName, '#') . '\b#';

        $newNameValue = Strings::replace($this->name, $oldNamePattern, $newName);
        if ($newNameValue !== $this->name) {
            $this->name = $newNameValue;
            return true;
        }

        return false;
    }
}
