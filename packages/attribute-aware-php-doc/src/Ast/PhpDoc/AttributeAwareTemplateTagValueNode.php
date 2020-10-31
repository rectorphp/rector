<?php

declare(strict_types=1);

namespace Rector\AttributeAwarePhpDoc\Ast\PhpDoc;

use Nette\Utils\Strings;
use PHPStan\PhpDocParser\Ast\PhpDoc\TemplateTagValueNode;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use Rector\BetterPhpDocParser\Attributes\Attribute\AttributeTrait;
use Rector\BetterPhpDocParser\Contract\PhpDocNode\AttributeAwareNodeInterface;

final class AttributeAwareTemplateTagValueNode extends TemplateTagValueNode implements AttributeAwareNodeInterface
{
    use AttributeTrait;

    /**
     * @var string
     * @see https://regex101.com/r/4WtsUS/1
     */
    private const AS_OF_PREPOSITOIN_REGEX = '#\s+(?<preposition>as|of)\s+#';

    /**
     * @var string
     */
    private $preposition;

    public function __construct(string $name, ?TypeNode $typeNode, string $description, string $originalContent)
    {
        parent::__construct($name, $typeNode, $description);

        $matches = Strings::match($originalContent, self::AS_OF_PREPOSITOIN_REGEX);
        $this->preposition = $matches['preposition'] ?? 'of';
    }

    public function __toString(): string
    {
        // @see https://github.com/rectorphp/rector/issues/3438
        # 'as'/'of'
        $bound = $this->bound !== null ? ' ' . $this->preposition . ' ' . $this->bound : '';

        $content = $this->name . $bound . ' ' . $this->description;
        return trim($content);
    }
}
