<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\Rector\General;

use PhpParser\Node;
use PhpParser\Node\Expr\StaticCall;
use PHPStan\Type\ObjectType;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use RectorPrefix20211231\TYPO3\CMS\Core\Utility\GeneralUtility;
use RectorPrefix20211231\Webmozart\Assert\Assert;
/**
 * @see \Ssch\TYPO3Rector\Tests\Rector\General\MethodGetInstanceToMakeInstanceCallRector\MethodGetInstanceToMakeInstanceCallRectorTest
 */
final class MethodGetInstanceToMakeInstanceCallRector extends \Rector\Core\Rector\AbstractRector implements \Rector\Core\Contract\Rector\ConfigurableRectorInterface
{
    /**
     * @var string
     */
    public const CLASSES_GET_INSTANCE_TO_MAKE_INSTANCE = 'classes-get-instance-to-make-instance';
    /**
     * @var array<string, string[]>
     */
    private const EXAMPLE_CONFIGURATION = [self::CLASSES_GET_INSTANCE_TO_MAKE_INSTANCE => ['SomeClass']];
    /**
     * @var string[]
     */
    private $classes = [];
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Expr\StaticCall::class];
    }
    /**
     * @param StaticCall $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if ($this->shouldSkip($node)) {
            return null;
        }
        $className = $this->nodeNameResolver->getName($node->class);
        if (null === $className) {
            return null;
        }
        $class = $this->nodeFactory->createClassConstReference($className);
        return $this->nodeFactory->createStaticCall(\RectorPrefix20211231\TYPO3\CMS\Core\Utility\GeneralUtility::class, 'makeInstance', [$class]);
    }
    /**
     * @codeCoverageIgnore
     */
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Use GeneralUtility::makeInstance instead of getInstance call', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample(<<<'CODE_SAMPLE'
$instance = TYPO3\CMS\Core\Resource\Index\ExtractorRegistry::getInstance();
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use TYPO3\CMS\Core\Resource\Index\ExtractorRegistry;

$instance = GeneralUtility::makeInstance(ExtractorRegistry::class);
CODE_SAMPLE
, self::EXAMPLE_CONFIGURATION)]);
    }
    /**
     * @param mixed[] $configuration
     */
    public function configure(array $configuration) : void
    {
        $classes = $configuration[self::CLASSES_GET_INSTANCE_TO_MAKE_INSTANCE] ?? $configuration;
        \RectorPrefix20211231\Webmozart\Assert\Assert::isArray($classes);
        \RectorPrefix20211231\Webmozart\Assert\Assert::allString($classes);
        $this->classes = $classes;
    }
    private function shouldSkip(\PhpParser\Node\Expr\StaticCall $node) : bool
    {
        if ([] === $this->classes) {
            return \true;
        }
        if (!$this->isName($node->name, 'getInstance')) {
            return \true;
        }
        foreach ($this->classes as $class) {
            if ($this->nodeTypeResolver->isMethodStaticCallOrClassMethodObjectType($node, new \PHPStan\Type\ObjectType($class))) {
                return \false;
            }
        }
        return \true;
    }
}
