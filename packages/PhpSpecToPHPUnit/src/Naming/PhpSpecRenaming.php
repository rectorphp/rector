<?php declare(strict_types=1);

namespace Rector\PhpSpecToPHPUnit\Naming;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Namespace_;
use Rector\Exception\ShouldNotHappenException;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PhpParser\Node\Resolver\NameResolver;
use Rector\Util\RectorStrings;
use Symplify\PackageBuilder\Strings\StringFormatConverter;

final class PhpSpecRenaming
{
    /**
     * @var StringFormatConverter
     */
    private $stringFormatConverter;

    /**
     * @var NameResolver
     */
    private $nameResolver;

    public function __construct(StringFormatConverter $stringFormatConverter, NameResolver $nameResolver)
    {
        $this->stringFormatConverter = $stringFormatConverter;
        $this->nameResolver = $nameResolver;
    }

    public function renameMethod(ClassMethod $classMethod): void
    {
        $name = $this->nameResolver->getName($classMethod);
        if ($name === null) {
            return;
        }

        if ($classMethod->isPrivate()) {
            return;
        }

        $name = $this->removeNamePrefixes($name);

        // from PhpSpec to PHPUnit method naming convention
        $name = $this->stringFormatConverter->underscoreAndHyphenToCamelCase($name);

        // add "test", so PHPUnit runs the method
        if (! Strings::startsWith($name, 'test')) {
            $name = 'test' . ucfirst($name);
        }

        $classMethod->name = new Identifier($name);
    }

    public function renameExtends(Class_ $class): void
    {
        $class->extends = new FullyQualified('PHPUnit\Framework\TestCase');
    }

    public function renameNamespace(Class_ $class): void
    {
        /** @var Namespace_ $namespace */
        $namespace = $class->getAttribute(AttributeKey::NAMESPACE_NODE);
        if ($namespace->name === null) {
            return;
        }

        $newNamespaceName = RectorStrings::removePrefixes($namespace->name->toString(), ['spec\\']);

        $namespace->name = new Name('Tests\\' . $newNamespaceName);
    }

    public function renameClass(Class_ $class): void
    {
        // anonymous class?
        if ($class->name === null) {
            throw new ShouldNotHappenException();
        }

        // 2. change class name
        $newClassName = RectorStrings::removeSuffixes($class->name->toString(), ['Spec']);
        $newTestClassName = $newClassName . 'Test';

        $class->name = new Identifier($newTestClassName);
    }

    public function resolveObjectPropertyName(Class_ $class): string
    {
        // anonymous class?
        if ($class->name === null) {
            throw new ShouldNotHappenException();
        }

        $bareClassName = RectorStrings::removeSuffixes($class->name->toString(), ['Spec', 'Test']);

        return lcfirst($bareClassName);
    }

    public function resolveTestedClass(Node $node): string
    {
        /** @var string $className */
        $className = $node->getAttribute(AttributeKey::CLASS_NAME);

        $newClassName = RectorStrings::removePrefixes($className, ['spec\\']);

        return RectorStrings::removeSuffixes($newClassName, ['Spec']);
    }

    private function removeNamePrefixes(string $name): string
    {
        $originalName = $name;

        $name = RectorStrings::removePrefixes(
            $name,
            ['it_should_have_', 'it_should_be', 'it_should_', 'it_is_', 'it_', 'is_']
        );

        return $name ?: $originalName;
    }
}
