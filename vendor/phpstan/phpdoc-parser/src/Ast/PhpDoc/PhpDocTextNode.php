<?php

declare (strict_types=1);
namespace PHPStan\PhpDocParser\Ast\PhpDoc;

use PHPStan\PhpDocParser\Ast\NodeAttributes;
class PhpDocTextNode implements \PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocChildNode
{
    use NodeAttributes;
    public string $text;
    public function __construct(string $text)
    {
        $this->text = $text;
    }
    public function __toString(): string
    {
        return $this->text;
    }
    /**
     * @param array<string, mixed> $properties
     */
    public static function __set_state(array $properties): self
    {
        $instance = new self($properties['text']);
        if (isset($properties['attributes'])) {
            foreach ($properties['attributes'] as $key => $value) {
                $instance->setAttribute($key, $value);
            }
        }
        return $instance;
    }
}
