<?php

declare (strict_types=1);
namespace Rector\Symfony\Symfony62\Rector\Class_;

use RectorPrefix202506\Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Reflection\ReflectionProvider;
use Rector\Rector\AbstractRector;
use Rector\Symfony\CodeQuality\NodeAnalyzer\AttributePresenceDetector;
use Rector\Symfony\Enum\SensioAttribute;
use Rector\Symfony\Enum\SymfonyAttribute;
use Rector\ValueObject\PhpVersionFeature;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://github.com/symfony/symfony/pull/45415#issuecomment-1077625813
 * @changelog https://github.com/symfony/symfony/pull/46907
 *
 * @see \Rector\Symfony\Tests\Symfony62\Rector\Class_\SecurityAttributeToIsGrantedAttributeRector\SecurityAttributeToIsGrantedAttributeRectorTest
 */
final class SecurityAttributeToIsGrantedAttributeRector extends AbstractRector implements MinPhpVersionInterface
{
    /**
     * @readonly
     */
    private ReflectionProvider $reflectionProvider;
    /**
     * @readonly
     */
    private AttributePresenceDetector $attributePresenceDetector;
    /**
     * @var string
     * @see https://regex101.com/r/Si1sDz/1
     */
    private const SOLE_IS_GRANTED_REGEX = '#^is_granted\\((\\"|\')(?<role>[\\w]+)(\\"|\')\\)$#';
    /**
     * @var string
     * @see https://regex101.com/r/NYRPrx/1
     */
    private const IS_GRANTED_AND_SUBJECT_REGEX = '#^is_granted\\((\\"|\')(?<role>[\\w]+)(\\"|\'),\\s+(?<subject>\\w+)\\)$#';
    public function __construct(ReflectionProvider $reflectionProvider, AttributePresenceDetector $attributePresenceDetector)
    {
        $this->reflectionProvider = $reflectionProvider;
        $this->attributePresenceDetector = $attributePresenceDetector;
    }
    public function provideMinPhpVersion() : int
    {
        return PhpVersionFeature::ATTRIBUTES;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Replaces #[Security] framework-bundle attribute with Symfony native #[IsGranted] one', [new CodeSample(<<<'CODE_SAMPLE'
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

class PostController extends Controller
{
    #[Security("is_granted('ROLE_ADMIN')")]
    public function index()
    {
    }

    #[Security("is_granted('ROLE_ADMIN') and is_granted('ROLE_FRIENDLY_USER')")]
    public function list()
    {
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class PostController extends Controller
{
    #[IsGranted(attribute: 'ROLE_ADMIN')]
    public function index()
    {
    }

    #[IsGranted(attribute: new Expression("is_granted('ROLE_ADMIN') and is_granted('ROLE_FRIENDLY_USER')"))]
    public function list()
    {
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [Class_::class, ClassMethod::class];
    }
    /**
     * @param Class_|ClassMethod $node
     */
    public function refactor(Node $node) : ?Node
    {
        if (!$this->attributePresenceDetector->detect(SensioAttribute::SECURITY)) {
            return null;
        }
        $hasChanged = \false;
        foreach ($node->attrGroups as $attrGroup) {
            foreach ($attrGroup->attrs as $attribute) {
                if (!$this->isName($attribute->name, SensioAttribute::SECURITY)) {
                    continue;
                }
                // 1. resolve closest existing name of IsGranted
                $isGrantedName = $this->resolveIsGrantedAttributeName();
                $attribute->name = new FullyQualified($isGrantedName);
                $firstArg = $attribute->args[0];
                $firstArg->name = new Identifier('attribute');
                $firstValue = $firstArg->value;
                if ($firstValue instanceof String_) {
                    $match = Strings::match($firstValue->value, self::IS_GRANTED_AND_SUBJECT_REGEX);
                    if ($match !== null) {
                        $firstArg->name = new Identifier('attribute');
                        $firstArg->value = new String_($match['role']);
                        $secondArg = new Arg(new String_($match['subject']));
                        $secondArg->name = new Identifier('subject');
                        $attribute->args[] = $secondArg;
                        $hasChanged = \true;
                        continue;
                    }
                    $match = Strings::match($firstValue->value, self::SOLE_IS_GRANTED_REGEX);
                    // for single role, return it directly
                    if (isset($match['role'])) {
                        $firstArg->value = new String_($match['role']);
                        $hasChanged = \true;
                        continue;
                    }
                }
                $attribute->args[0]->value = $this->wrapToNewExpression($firstArg->value);
                $hasChanged = \true;
            }
        }
        if ($hasChanged) {
            return $node;
        }
        return null;
    }
    /**
     * @return \PhpParser\Node\Expr\New_|\PhpParser\Node\Scalar\String_
     */
    private function wrapToNewExpression(Expr $expr)
    {
        if ($expr instanceof String_) {
            $match = Strings::match($expr->value, self::SOLE_IS_GRANTED_REGEX);
            // for single role, return it directly
            if (isset($match['role'])) {
                return new String_($match['role']);
            }
        }
        $args = [new Arg($expr)];
        return new New_(new FullyQualified('Symfony\\Component\\ExpressionLanguage\\Expression'), $args);
    }
    private function resolveIsGrantedAttributeName() : string
    {
        if ($this->reflectionProvider->hasClass(SymfonyAttribute::IS_GRANTED)) {
            return SymfonyAttribute::IS_GRANTED;
        }
        // fallback to "sensio"
        return SensioAttribute::IS_GRANTED;
    }
}
