<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDoc;

use Nette\Utils\Strings;
use PHPStan\PhpDocParser\Ast\NodeAttributes;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagValueNode;

final class DoctrineAnnotationTagValueNode implements PhpDocTagValueNode
{
    use NodeAttributes;

    /**
     * @var string
     */
    private $tagClass;

    /**
     * @var string
     */
    private $docContent;

    /**
     * @var array<mixed, mixed>
     */
    private $values = [];

    /**
     * @param array<mixed, mixed> $values
     */
    public function __construct(string $tagClass, string $docContent, array $values)
    {
        $this->tagClass = $tagClass;
        $this->docContent = $docContent;
        $this->values = $values;
    }

    public function __toString(): string
    {
        // without modifications, @todo split into items if needed
        return $this->docContent;
    }

    public function getTagClass(): string
    {
        return $this->tagClass;
    }

    public function getDocContent(): string
    {
        return $this->docContent;
    }

    public function getValues(): array
    {
        return $this->values;
    }

    /**
     * @todo should be type of \PHPStan\PhpDocParser\Ast\Node
     * @return mixed|null
     */
    public function getValue(string $key)
    {
        return $this->values[$key] ?? null;
    }


    public function getValueWithoutQuotes(string $key)
    {
        $value = $this->getValue($key);
        if ($value === null) {
            return null;
        }

        $matches = Strings::match($value, '#"(?<content>.*?)"#');
        if ($matches === null) {
            return $value;
        }

        return $matches['content'];
    }
}
