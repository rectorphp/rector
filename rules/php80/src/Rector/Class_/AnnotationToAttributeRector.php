<?php

declare(strict_types=1);

namespace Rector\Php80\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Property;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\PhpAttribute\AnnotationToAttributeConverter;

/**
 * @see https://wiki.php.net/rfc/attributes_v2
 *
 * @see \Rector\Php80\Tests\Rector\Class_\AnnotationToAttributeRector\AnnotationToAttributeRectorTest
 */
final class AnnotationToAttributeRector extends AbstractRector
{
    /**
     * @var AnnotationToAttributeConverter
     */
    private $annotationToAttributeConverter;

    public function __construct(AnnotationToAttributeConverter $annotationToAttributeConverter)
    {
        $this->annotationToAttributeConverter = $annotationToAttributeConverter;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Change annotation to attribute', [
            new CodeSample(
                <<<'PHP'
use Doctrine\ORM\Attributes as ORM;

/**
  * @ORM\Entity
  */
class SomeClass
{
}
PHP
,
                <<<'PHP'
use Doctrine\ORM\Attributes as ORM;

<<ORM\Entity>>
class SomeClass
{
}
PHP
            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [Class_::class, ClassMethod::class, Property::class, FunctionLike::class];
    }

    /**
     * @param Class_|ClassMethod|Property $node
     */
    public function refactor(Node $node): ?Node
    {
        return $this->annotationToAttributeConverter->convertNode($node);
    }
}
