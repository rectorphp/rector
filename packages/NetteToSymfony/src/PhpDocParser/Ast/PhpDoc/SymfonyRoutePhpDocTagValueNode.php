<?php declare(strict_types=1);

namespace Rector\NetteToSymfony\PhpDocParser\Ast\PhpDoc;

use Rector\BetterPhpDocParser\PhpDocParser\Ast\PhpDoc\AbstractTagValueNode;

final class SymfonyRoutePhpDocTagValueNode extends AbstractTagValueNode
{
    /**
     * @var string
     */
    public const SHORT_NAME = '@Route';

    /**
     * @var string
     */
    public const CLASS_NAME = 'Symfony\Component\Routing\Annotation\Route';

    /**
     * @var string|null
     */
    public $name;

    /**
     * @var string
     */
    private $path;

    /**
     * @var string[]
     */
    private $methods = [];

    /**
     * @param string[] $methods
     */
    public function __construct(string $path, ?string $name = null, array $methods = [])
    {
        $this->path = $path;
        $this->name = $name;
        $this->methods = $methods;
    }

    public function __toString(): string
    {
        $contentItems = [
            'path' => sprintf('path="%s"', $this->path),
        ];

        if ($this->name) {
            $contentItems['name'] = sprintf('name="%s"', $this->name);
        }

        if ($this->methods) {
            $contentItems['methods'] = $this->printArrayItem($this->methods, 'methods');
        }

        return $this->printContentItems($contentItems);
    }
}
