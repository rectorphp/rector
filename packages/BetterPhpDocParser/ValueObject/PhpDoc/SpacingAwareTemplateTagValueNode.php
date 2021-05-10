<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\ValueObject\PhpDoc;

use PHPStan\PhpDocParser\Ast\PhpDoc\TemplateTagValueNode;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;

final class SpacingAwareTemplateTagValueNode extends TemplateTagValueNode
{
    public function __construct(
        string $name,
        ?TypeNode $typeNode,
        string $description,
        private string $preposition
    ) {
        parent::__construct($name, $typeNode, $description);
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
