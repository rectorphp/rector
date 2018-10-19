<?php declare(strict_types=1);

namespace Rector\Jms\Rector\Property;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Stmt\Property;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use Rector\Bridge\Contract\AnalyzedApplicationContainerInterface;
use Rector\NodeTypeResolver\Node\Attribute;
use Rector\NodeTypeResolver\PhpDoc\NodeAnalyzer\DocBlockAnalyzer;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * @see https://jmsyst.com/bundles/JMSDiExtraBundle/master/annotations#inject
 */
final class JmsInjectAnnotationRector extends AbstractRector
{
    /**
     * @var string
     */
    private const INJECT_ANNOTATION = 'JMS\DiExtraBundle\Annotation\Inject';

    /**
     * @var DocBlockAnalyzer
     */
    private $docBlockAnalyzer;

    /**
     * @var AnalyzedApplicationContainerInterface
     */
    private $analyzedApplicationContainer;

    public function __construct(
        DocBlockAnalyzer $docBlockAnalyzer,
        AnalyzedApplicationContainerInterface $analyzedApplicationContainer
    ) {
        $this->docBlockAnalyzer = $docBlockAnalyzer;
        $this->analyzedApplicationContainer = $analyzedApplicationContainer;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Changes properties with @JMS\DiExtraBundle\Annotation\Inject to constructor injection',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
use JMS\DiExtraBundle\Annotation as DI;

class SomeController
{
    /**
     * @DI\Inject("entity.manager")
     */
    private $entityManager;
}
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
use JMS\DiExtraBundle\Annotation as DI;

class SomeController
{
    /**
     * @var EntityManager
     */
    private $entityManager;
}
CODE_SAMPLE
                ),
            ]
        );
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [Property::class];
    }

    /**
     * @param Property $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->docBlockAnalyzer->hasTag($node, self::INJECT_ANNOTATION)) {
            return null;
        }

        $type = $this->resolveType($node);
        if ($type === null) {
            return null;
        }

        if (! $this->docBlockAnalyzer->hasTag($node, 'var')) {
            $this->docBlockAnalyzer->addVarTag($node, $type);
        }

        $this->docBlockAnalyzer->removeTagFromNode($node, self::INJECT_ANNOTATION);

        $this->addPropertyToClass(
            (string) $node->getAttribute(Attribute::CLASS_NAME),
            $type,
            (string) $node->props[0]->name
        );

        return $node;
    }

    private function resolveType(Node $node): ?string
    {
        /** @var PhpDocTagNode $injectTagNode */
        $injectTagNode = $this->docBlockAnalyzer->getTagByName($node, self::INJECT_ANNOTATION);

        $serviceName = $this->resolveServiceNameFromInjectTag($injectTagNode);
        if ($serviceName) {
            if ($this->analyzedApplicationContainer->hasService($serviceName)) {
                return $this->analyzedApplicationContainer->getTypeForName($serviceName);
            }
        }

        $varTypes = $this->docBlockAnalyzer->getVarTypes($node);
        if (! count($varTypes)) {
            return null;
        }

        return array_shift($varTypes);
    }

    private function resolveServiceNameFromInjectTag(PhpDocTagNode $phpDocTagNode): ?string
    {
        $injectTagContent = (string) $phpDocTagNode->value;
        $match = Strings::match($injectTagContent, '#(\'|")(?<serviceName>.*?)(\'|")#');

        return $match['serviceName'] ?? null;
    }
}
