<?php declare(strict_types=1);

namespace Rector\ReflectionDocBlock\DocBlock;

use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\DocBlock\Serializer;
use Symplify\TokenRunner\ReflectionDocBlock\FixedSerializer;

final class TidingSerializer
{
    /**
     * @var Serializer
     */
    private $serializer;

    public function __construct(FixedSerializer $fixedSerializer)
    {
        $this->fixedSerializer = $fixedSerializer;
    }

    public function getDocComment(DocBlock $docBlock): string
    {
        $docComment = $this->fixedSerializer->getDocComment($docBlock);

        return $this->clearUnnededPreslashes($docComment);
    }

    /**
     * phpDocumentor adds extra preslashes,
     * maybe could be resolved with custom @see DocBlock\Tags\Formatter instead of @see PassthroughFormatter
     */
    private function clearUnnededPreslashes(string $content): string
    {
        $content = str_replace('@var \\', '@var ', $content);

        return str_replace('@param \\', '@param ', $content);
    }
}
