<?php declare(strict_types=1);

namespace Rector\NetteToSymfony\Annotation;

use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocChildNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use Rector\BetterPhpDocParser\Attributes\Attribute\AttributeTrait;
use Rector\BetterPhpDocParser\Attributes\Contract\Ast\AttributeAwareNodeInterface;

final class SymfonyRoutePhpDocTagNode extends PhpDocTagNode implements PhpDocChildNode, AttributeAwareNodeInterface
{
    use AttributeTrait;

    /**
     * @var string|null
     */
    public $name;

    /**
     * @var string
     */
    private $path;

    /**
     * @var string
     */
    private $routeClass;

    /**
     * @var string[]
     */
    private $methods = [];

    /**
     * @param string[] $methods
     */
    public function __construct(string $routeClass, string $path, ?string $name = null, array $methods = [])
    {
        $this->path = $path;
        $this->name = $name;
        $this->routeClass = $routeClass;
        $this->methods = $methods;
    }

    public function __toString(): string
    {
        $content = sprintf('@\\%s(', $this->routeClass);

        $content .= sprintf('path="%s"', $this->path);

        if ($this->name) {
            $content .= sprintf(', name="%s"', $this->name);
        }

        if ($this->methods !== []) {
            $content .= sprintf(', methods={"%s"}', implode('", "', $this->methods));
        }

        return $content . ')';
    }
}
