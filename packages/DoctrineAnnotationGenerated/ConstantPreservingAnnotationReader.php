<?php

namespace Rector\DoctrineAnnotationGenerated;

use Doctrine\Common\Annotations\Annotation\IgnoreAnnotation;
use Doctrine\Common\Annotations\Annotation\Target;
use ReflectionClass;
use ReflectionFunction;
use ReflectionMethod;
use ReflectionProperty;
use function array_merge;
use function class_exists;
use function extension_loaded;
use function ini_get;
/**
 * A reader for docblock annotations.
 */
class ConstantPreservingAnnotationReader implements \Doctrine\Common\Annotations\Reader
{
    /**
     * Global map for imports.
     *
     * @var array<string, class-string>
     */
    private static $globalImports = ['ignoreannotation' => \Doctrine\Common\Annotations\Annotation\IgnoreAnnotation::class];
    /**
     * A list with annotations that are not causing exceptions when not resolved to an annotation class.
     *
     * The names are case sensitive.
     *
     * @var array<string, true>
     */
    private static $globalIgnoredNames = \Doctrine\Common\Annotations\ImplicitlyIgnoredAnnotationNames::LIST;
    /**
     * A list with annotations that are not causing exceptions when not resolved to an annotation class.
     *
     * The names are case sensitive.
     *
     * @var array<string, true>
     */
    private static $globalIgnoredNamespaces = [];
    /**
     * Add a new annotation to the globally ignored annotation names with regard to exception handling.
     *
     * @param string $name
     */
    public static function addGlobalIgnoredName($name)
    {
        self::$globalIgnoredNames[$name] = true;
    }
    /**
     * Add a new annotation to the globally ignored annotation namespaces with regard to exception handling.
     *
     * @param string $namespace
     */
    public static function addGlobalIgnoredNamespace($namespace)
    {
        self::$globalIgnoredNamespaces[$namespace] = true;
    }
    /**
     * Annotations parser.
     *
     * @var DocParser
     */
    private $parser;
    /**
     * Annotations parser used to collect parsing metadata.
     *
     * @var DocParser
     */
    private $preParser;
    /**
     * PHP parser used to collect imports.
     *
     * @var PhpParser
     */
    private $phpParser;
    /**
     * In-memory cache mechanism to store imported annotations per class.
     *
     * @psalm-var array<'class'|'function', array<string, array<string, class-string>>>
     */
    private $imports = [];
    /**
     * In-memory cache mechanism to store ignored annotations per class.
     *
     * @psalm-var array<'class'|'function', array<string, array<string, true>>>
     */
    private $ignoredAnnotationNames = [];
    /**
     * Initializes a new AnnotationReader.
     *
     * @throws AnnotationException
     */
    public function __construct(\Rector\DoctrineAnnotationGenerated\ConstantPreservingDocParser $parser = null)
    {
        if (\extension_loaded('Zend Optimizer+') && (\ini_get('zend_optimizerplus.save_comments') === '0' || \ini_get('opcache.save_comments') === '0')) {
            throw \Doctrine\Common\Annotations\AnnotationException::optimizerPlusSaveComments();
        }
        if (\extension_loaded('Zend OPcache') && \ini_get('opcache.save_comments') === 0) {
            throw \Doctrine\Common\Annotations\AnnotationException::optimizerPlusSaveComments();
        }
        // Make sure that the IgnoreAnnotation annotation is loaded
        \class_exists(\Doctrine\Common\Annotations\Annotation\IgnoreAnnotation::class);
        $this->parser = $parser ?: new \Doctrine\Common\Annotations\DocParser();
        $this->preParser = new \Rector\DoctrineAnnotationGenerated\ConstantPreservingDocParser();
        $this->preParser->setImports(self::$globalImports);
        $this->preParser->setIgnoreNotImportedAnnotations(true);
        $this->preParser->setIgnoredAnnotationNames(self::$globalIgnoredNames);
        $this->phpParser = new \Doctrine\Common\Annotations\PhpParser();
    }
    /**
     * {@inheritDoc}
     */
    public function getClassAnnotations(\ReflectionClass $class)
    {
        $this->parser->setTarget(\Doctrine\Common\Annotations\Annotation\Target::TARGET_CLASS);
        $this->parser->setImports($this->getImports($class));
        $this->parser->setIgnoredAnnotationNames($this->getIgnoredAnnotationNames($class));
        $this->parser->setIgnoredAnnotationNamespaces(self::$globalIgnoredNamespaces);
        return $this->parser->parse($class->getDocComment(), 'class ' . $class->getName());
    }
    /**
     * {@inheritDoc}
     */
    public function getClassAnnotation(\ReflectionClass $class, $annotationName)
    {
        $annotations = $this->getClassAnnotations($class);
        foreach ($annotations as $annotation) {
            if ($annotation instanceof $annotationName) {
                return $annotation;
            }
        }
        return null;
    }
    /**
     * {@inheritDoc}
     */
    public function getPropertyAnnotations(\ReflectionProperty $property)
    {
        $class = $property->getDeclaringClass();
        $context = 'property ' . $class->getName() . '::$' . $property->getName();
        $this->parser->setTarget(\Doctrine\Common\Annotations\Annotation\Target::TARGET_PROPERTY);
        $this->parser->setImports($this->getPropertyImports($property));
        $this->parser->setIgnoredAnnotationNames($this->getIgnoredAnnotationNames($class));
        $this->parser->setIgnoredAnnotationNamespaces(self::$globalIgnoredNamespaces);
        return $this->parser->parse($property->getDocComment(), $context);
    }
    /**
     * {@inheritDoc}
     */
    public function getPropertyAnnotation(\ReflectionProperty $property, $annotationName)
    {
        $annotations = $this->getPropertyAnnotations($property);
        foreach ($annotations as $annotation) {
            if ($annotation instanceof $annotationName) {
                return $annotation;
            }
        }
        return null;
    }
    /**
     * {@inheritDoc}
     */
    public function getMethodAnnotations(\ReflectionMethod $method)
    {
        $class = $method->getDeclaringClass();
        $context = 'method ' . $class->getName() . '::' . $method->getName() . '()';
        $this->parser->setTarget(\Doctrine\Common\Annotations\Annotation\Target::TARGET_METHOD);
        $this->parser->setImports($this->getMethodImports($method));
        $this->parser->setIgnoredAnnotationNames($this->getIgnoredAnnotationNames($class));
        $this->parser->setIgnoredAnnotationNamespaces(self::$globalIgnoredNamespaces);
        return $this->parser->parse($method->getDocComment(), $context);
    }
    /**
     * {@inheritDoc}
     */
    public function getMethodAnnotation(\ReflectionMethod $method, $annotationName)
    {
        $annotations = $this->getMethodAnnotations($method);
        foreach ($annotations as $annotation) {
            if ($annotation instanceof $annotationName) {
                return $annotation;
            }
        }
        return null;
    }
    /**
     * Gets the annotations applied to a function.
     *
     * @phpstan-return list<object> An array of Annotations.
     */
    public function getFunctionAnnotations(\ReflectionFunction $function): array
    {
        $context = 'function ' . $function->getName();
        $this->parser->setTarget(\Doctrine\Common\Annotations\Annotation\Target::TARGET_FUNCTION);
        $this->parser->setImports($this->getImports($function));
        $this->parser->setIgnoredAnnotationNames($this->getIgnoredAnnotationNames($function));
        $this->parser->setIgnoredAnnotationNamespaces(self::$globalIgnoredNamespaces);
        return $this->parser->parse($function->getDocComment(), $context);
    }
    /**
     * Gets a function annotation.
     *
     * @return object|null The Annotation or NULL, if the requested annotation does not exist.
     */
    public function getFunctionAnnotation(\ReflectionFunction $function, string $annotationName)
    {
        $annotations = $this->getFunctionAnnotations($function);
        foreach ($annotations as $annotation) {
            if ($annotation instanceof $annotationName) {
                return $annotation;
            }
        }
        return null;
    }
    /**
     * Returns the ignored annotations for the given class or function.
     *
     * @param ReflectionClass|ReflectionFunction $reflection
     *
     * @return array<string, true>
     */
    private function getIgnoredAnnotationNames($reflection): array
    {
        $type = $reflection instanceof \ReflectionClass ? 'class' : 'function';
        $name = $reflection->getName();
        if (isset($this->ignoredAnnotationNames[$type][$name])) {
            return $this->ignoredAnnotationNames[$type][$name];
        }
        $this->collectParsingMetadata($reflection);
        return $this->ignoredAnnotationNames[$type][$name];
    }
    /**
     * Retrieves imports for a class or a function.
     *
     * @param ReflectionClass|ReflectionFunction $reflection
     *
     * @return array<string, class-string>
     */
    private function getImports($reflection): array
    {
        $type = $reflection instanceof \ReflectionClass ? 'class' : 'function';
        $name = $reflection->getName();
        if (isset($this->imports[$type][$name])) {
            return $this->imports[$type][$name];
        }
        $this->collectParsingMetadata($reflection);
        return $this->imports[$type][$name];
    }
    /**
     * Retrieves imports for methods.
     *
     * @return array<string, class-string>
     */
    private function getMethodImports(\ReflectionMethod $method)
    {
        $class = $method->getDeclaringClass();
        $classImports = $this->getImports($class);
        $traitImports = [];
        foreach ($class->getTraits() as $trait) {
            if (!$trait->hasMethod($method->getName()) || $trait->getFileName() !== $method->getFileName()) {
                continue;
            }
            $traitImports = \array_merge($traitImports, $this->phpParser->parseUseStatements($trait));
        }
        return \array_merge($classImports, $traitImports);
    }
    /**
     * Retrieves imports for properties.
     *
     * @return array<string, class-string>
     */
    private function getPropertyImports(\ReflectionProperty $property)
    {
        $class = $property->getDeclaringClass();
        $classImports = $this->getImports($class);
        $traitImports = [];
        foreach ($class->getTraits() as $trait) {
            if (!$trait->hasProperty($property->getName())) {
                continue;
            }
            $traitImports = \array_merge($traitImports, $this->phpParser->parseUseStatements($trait));
        }
        return \array_merge($classImports, $traitImports);
    }
    /**
     * Collects parsing metadata for a given class or function.
     *
     * @param ReflectionClass|ReflectionFunction $reflection
     */
    private function collectParsingMetadata($reflection): void
    {
        $type = $reflection instanceof \ReflectionClass ? 'class' : 'function';
        $name = $reflection->getName();
        $ignoredAnnotationNames = self::$globalIgnoredNames;
        $annotations = $this->preParser->parse($reflection->getDocComment(), $type . ' ' . $name);
        foreach ($annotations as $annotation) {
            if (!$annotation instanceof \Doctrine\Common\Annotations\Annotation\IgnoreAnnotation) {
                continue;
            }
            foreach ($annotation->names as $annot) {
                $ignoredAnnotationNames[$annot] = true;
            }
        }
        $this->imports[$type][$name] = \array_merge(self::$globalImports, $this->phpParser->parseUseStatements($reflection), ['__NAMESPACE__' => $reflection->getNamespaceName(), 'self' => $name]);
        $this->ignoredAnnotationNames[$type][$name] = $ignoredAnnotationNames;
    }
}
