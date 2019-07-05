<?php declare(strict_types=1);

namespace Rector\Architecture\Rector\Class_;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PHPStan\PhpDocParser\Ast\PhpDoc\GenericTagValueNode;
use Rector\Architecture\Tests\Rector\Class_\RemoveRepositoryFromEntityAnnotationRector\RemoveRepositoryFromEntityAnnotationRectorTest;
use Rector\NodeTypeResolver\PhpDoc\NodeAnalyzer\DocBlockManipulator;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * @see RemoveRepositoryFromEntityAnnotationRectorTest
 */
final class RemoveRepositoryFromEntityAnnotationRector extends AbstractRector
{
    /**
     * @var string
     */
    private const DOCTRINE_ORM_MAPPING_ENTITY = 'Doctrine\ORM\Mapping\Entity';

    /**
     * @var DocBlockManipulator
     */
    private $docBlockManipulator;

    public function __construct(DocBlockManipulator $docBlockManipulator)
    {
        $this->docBlockManipulator = $docBlockManipulator;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Removes repository class from @Entity annotation', [
            new CodeSample(
                <<<'CODE_SAMPLE'
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="ProductRepository")
 */
class Product
{
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class Product
{
}
CODE_SAMPLE
            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [Class_::class];
    }

    /**
     * @param Class_ $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($node->getDocComment() === null) {
            return null;
        }

        $phpDocInfo = $this->docBlockManipulator->createPhpDocInfoFromNode($node);
        if (! $phpDocInfo->hasTag(self::DOCTRINE_ORM_MAPPING_ENTITY)) {
            return null;
        }

        $entityTags = $phpDocInfo->getTagsByName(self::DOCTRINE_ORM_MAPPING_ENTITY);
        if ($entityTags === []) {
            return null;
        }

        $entityTag = $entityTags[0];
        if (! $entityTag->value instanceof GenericTagValueNode) {
            return null;
        }

        $entityTag->value->value = Strings::replace($entityTag->value->value, '#\(repositoryClass="(.*?)"\)#');

        // save the entity tag
        $this->docBlockManipulator->updateNodeWithPhpDocInfo($node, $phpDocInfo);

        return $node;
    }
}
