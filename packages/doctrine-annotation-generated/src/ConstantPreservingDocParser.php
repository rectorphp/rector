<?php

namespace Rector\DoctrineAnnotationGenerated;

use Doctrine\Common\Annotations\Annotation\Attribute;
use Doctrine\Common\Annotations\Annotation\Attributes;
use Doctrine\Common\Annotations\Annotation\Enum;
use Doctrine\Common\Annotations\Annotation\Target;
use ReflectionClass;
use ReflectionException;
use ReflectionProperty;
use RuntimeException;
use stdClass;
use function array_keys;
use function array_map;
use function class_exists;
use function constant;
use function defined;
use function explode;
use function gettype;
use function implode;
use function in_array;
use function interface_exists;
use function is_array;
use function is_object;
use function is_subclass_of;
use function json_encode;
use function ltrim;
use function preg_match;
use function reset;
use function rtrim;
use function sprintf;
use function stripos;
use function strlen;
use function strpos;
use function strrpos;
use function strtolower;
use function substr;
use function trim;
use const PHP_VERSION_ID;
/**
 * A parser for docblock annotations.
 *
 * It is strongly discouraged to change the default annotation parsing process.
 */
final class ConstantPreservingDocParser
{
    /**
     * An array of all valid tokens for a class name.
     *
     * @phpstan-var list<int>
     */
    private static $classIdentifiers = [\Doctrine\Common\Annotations\DocLexer::T_IDENTIFIER, \Doctrine\Common\Annotations\DocLexer::T_TRUE, \Doctrine\Common\Annotations\DocLexer::T_FALSE, \Doctrine\Common\Annotations\DocLexer::T_NULL];
    /**
     * The lexer.
     *
     * @var DocLexer
     */
    private $lexer;
    /**
     * Current target context.
     *
     * @var int
     */
    private $target;
    /**
     * Doc parser used to collect annotation target.
     *
     * @var DocParser
     */
    private static $metadataParser;
    /**
     * Flag to control if the current annotation is nested or not.
     *
     * @var bool
     */
    private $isNestedAnnotation = false;
    /**
     * Hashmap containing all use-statements that are to be used when parsing
     * the given doc block.
     *
     * @var array<string, class-string>
     */
    private $imports = [];
    /**
     * This hashmap is used internally to cache results of class_exists()
     * look-ups.
     *
     * @var array<class-string, bool>
     */
    private $classExists = [];
    /**
     * Whether annotations that have not been imported should be ignored.
     *
     * @var bool
     */
    private $ignoreNotImportedAnnotations = false;
    /**
     * An array of default namespaces if operating in simple mode.
     *
     * @var string[]
     */
    private $namespaces = [];
    /**
     * A list with annotations that are not causing exceptions when not resolved to an annotation class.
     *
     * The names must be the raw names as used in the class, not the fully qualified
     *
     * @var bool[] indexed by annotation name
     */
    private $ignoredAnnotationNames = [];
    /**
     * A list with annotations in namespaced format
     * that are not causing exceptions when not resolved to an annotation class.
     *
     * @var bool[] indexed by namespace name
     */
    private $ignoredAnnotationNamespaces = [];
    /** @var string */
    private $context = '';
    /**
     * Hash-map for caching annotation metadata.
     *
     * @var array<class-string, mixed[]>
     */
    private static $annotationMetadata = [\Doctrine\Common\Annotations\Annotation\Target::class => ['is_annotation' => true, 'has_constructor' => true, 'properties' => [], 'targets_literal' => 'ANNOTATION_CLASS', 'targets' => \Doctrine\Common\Annotations\Annotation\Target::TARGET_CLASS, 'default_property' => 'value', 'attribute_types' => ['value' => ['required' => false, 'type' => 'array', 'array_type' => 'string', 'value' => 'array<string>']]], \Doctrine\Common\Annotations\Annotation\Attribute::class => ['is_annotation' => true, 'has_constructor' => false, 'targets_literal' => 'ANNOTATION_ANNOTATION', 'targets' => \Doctrine\Common\Annotations\Annotation\Target::TARGET_ANNOTATION, 'default_property' => 'name', 'properties' => ['name' => 'name', 'type' => 'type', 'required' => 'required'], 'attribute_types' => ['value' => ['required' => true, 'type' => 'string', 'value' => 'string'], 'type' => ['required' => true, 'type' => 'string', 'value' => 'string'], 'required' => ['required' => false, 'type' => 'boolean', 'value' => 'boolean']]], \Doctrine\Common\Annotations\Annotation\Attributes::class => ['is_annotation' => true, 'has_constructor' => false, 'targets_literal' => 'ANNOTATION_CLASS', 'targets' => \Doctrine\Common\Annotations\Annotation\Target::TARGET_CLASS, 'default_property' => 'value', 'properties' => ['value' => 'value'], 'attribute_types' => ['value' => ['type' => 'array', 'required' => true, 'array_type' => \Doctrine\Common\Annotations\Annotation\Attribute::class, 'value' => 'array<' . \Doctrine\Common\Annotations\Annotation\Attribute::class . '>']]], \Doctrine\Common\Annotations\Annotation\Enum::class => ['is_annotation' => true, 'has_constructor' => true, 'targets_literal' => 'ANNOTATION_PROPERTY', 'targets' => \Doctrine\Common\Annotations\Annotation\Target::TARGET_PROPERTY, 'default_property' => 'value', 'properties' => ['value' => 'value'], 'attribute_types' => ['value' => ['type' => 'array', 'required' => true], 'literal' => ['type' => 'array', 'required' => false]]]];
    /**
     * Hash-map for handle types declaration.
     *
     * @var array<string, string>
     */
    private static $typeMap = [
        'float' => 'double',
        'bool' => 'boolean',
        // allow uppercase Boolean in honor of George Boole
        'Boolean' => 'boolean',
        'int' => 'integer',
    ];
    /**
     * Constructs a new DocParser.
     */
    public function __construct()
    {
        $this->lexer = new \Doctrine\Common\Annotations\DocLexer();
    }
    /**
     * Sets the annotation names that are ignored during the parsing process.
     *
     * The names are supposed to be the raw names as used in the class, not the
     * fully qualified class names.
     *
     * @param bool[] $names indexed by annotation name
     *
     * @return void
     */
    public function setIgnoredAnnotationNames(array $names)
    {
        $this->ignoredAnnotationNames = $names;
    }
    /**
     * Sets the annotation namespaces that are ignored during the parsing process.
     *
     * @param bool[] $ignoredAnnotationNamespaces indexed by annotation namespace name
     *
     * @return void
     */
    public function setIgnoredAnnotationNamespaces($ignoredAnnotationNamespaces)
    {
        $this->ignoredAnnotationNamespaces = $ignoredAnnotationNamespaces;
    }
    /**
     * Sets ignore on not-imported annotations.
     *
     * @param bool $bool
     *
     * @return void
     */
    public function setIgnoreNotImportedAnnotations($bool)
    {
        $this->ignoreNotImportedAnnotations = (bool) $bool;
    }
    /**
     * Sets the default namespaces.
     *
     * @param string $namespace
     *
     * @return void
     *
     * @throws RuntimeException
     */
    public function addNamespace($namespace)
    {
        if ($this->imports) {
            throw new \RuntimeException('You must either use addNamespace(), or setImports(), but not both.');
        }
        $this->namespaces[] = $namespace;
    }
    /**
     * Sets the imports.
     *
     * @param array<string, class-string> $imports
     *
     * @return void
     *
     * @throws RuntimeException
     */
    public function setImports(array $imports)
    {
        if ($this->namespaces) {
            throw new \RuntimeException('You must either use addNamespace(), or setImports(), but not both.');
        }
        $this->imports = $imports;
    }
    /**
     * Sets current target context as bitmask.
     *
     * @param int $target
     *
     * @return void
     */
    public function setTarget($target)
    {
        $this->target = $target;
    }
    /**
     * Parses the given docblock string for annotations.
     *
     * @param string $input   The docblock string to parse.
     * @param string $context The parsing context.
     *
     * @throws AnnotationException
     * @throws ReflectionException
     *
     * @phpstan-return list<object> Array of annotations. If no annotations are found, an empty array is returned.
     */
    public function parse($input, $context = '')
    {
        $pos = $this->findInitialTokenPosition($input);
        if ($pos === null) {
            return [];
        }
        $this->context = $context;
        $this->lexer->setInput(\trim(\substr($input, $pos), '* /'));
        $this->lexer->moveNext();
        return $this->Annotations();
    }
    /**
     * Finds the first valid annotation
     *
     * @param string $input The docblock string to parse
     */
    private function findInitialTokenPosition($input): ?int
    {
        $pos = 0;
        // search for first valid annotation
        while (($pos = \strpos($input, '@', $pos)) !== false) {
            $preceding = \substr($input, $pos - 1, 1);
            // if the @ is preceded by a space, a tab or * it is valid
            if ($pos === 0 || $preceding === ' ' || $preceding === '*' || $preceding === "\t") {
                return $pos;
            }
            $pos++;
        }
        return null;
    }
    /**
     * Attempts to match the given token with the current lookahead token.
     * If they match, updates the lookahead token; otherwise raises a syntax error.
     *
     * @param int $token Type of token.
     *
     * @return bool True if tokens match; false otherwise.
     *
     * @throws AnnotationException
     */
    private function match(int $token): bool
    {
        if (!$this->lexer->isNextToken($token)) {
            throw $this->syntaxError($this->lexer->getLiteral($token));
        }
        return $this->lexer->moveNext();
    }
    /**
     * Attempts to match the current lookahead token with any of the given tokens.
     *
     * If any of them matches, this method updates the lookahead token; otherwise
     * a syntax error is raised.
     *
     * @throws AnnotationException
     *
     * @phpstan-param list<mixed[]> $tokens
     */
    private function matchAny(array $tokens): bool
    {
        if (!$this->lexer->isNextTokenAny($tokens)) {
            throw $this->syntaxError(\implode(' or ', \array_map([$this->lexer, 'getLiteral'], $tokens)));
        }
        return $this->lexer->moveNext();
    }
    /**
     * Generates a new syntax error.
     *
     * @param string       $expected Expected string.
     * @param mixed[]|null $token    Optional token.
     */
    private function syntaxError(string $expected, ?array $token = null): \Doctrine\Common\Annotations\AnnotationException
    {
        if ($token === null) {
            $token = $this->lexer->lookahead;
        }
        $message = \sprintf('Expected %s, got ', $expected);
        $message .= $this->lexer->lookahead === null ? 'end of string' : \sprintf("'%s' at position %s", $token['value'], $token['position']);
        if (\strlen($this->context)) {
            $message .= ' in ' . $this->context;
        }
        $message .= '.';
        return \Doctrine\Common\Annotations\AnnotationException::syntaxError($message);
    }
    /**
     * Attempts to check if a class exists or not. This never goes through the PHP autoloading mechanism
     * but uses the {@link AnnotationRegistry} to load classes.
     *
     * @param class-string $fqcn
     */
    private function classExists(string $fqcn): bool
    {
        if (isset($this->classExists[$fqcn])) {
            return $this->classExists[$fqcn];
        }
        // first check if the class already exists, maybe loaded through another AnnotationReader
        if (\class_exists($fqcn, false)) {
            return $this->classExists[$fqcn] = true;
        }
        // final check, does this class exist?
        return $this->classExists[$fqcn] = \Doctrine\Common\Annotations\AnnotationRegistry::loadAnnotationClass($fqcn);
    }
    /**
     * Collects parsing metadata for a given annotation class
     *
     * @param class-string $name The annotation name
     *
     * @throws AnnotationException
     * @throws ReflectionException
     */
    private function collectAnnotationMetadata(string $name): void
    {
        if (self::$metadataParser === null) {
            self::$metadataParser = new self();
            self::$metadataParser->setIgnoreNotImportedAnnotations(true);
            self::$metadataParser->setIgnoredAnnotationNames($this->ignoredAnnotationNames);
            self::$metadataParser->setImports(['enum' => \Doctrine\Common\Annotations\Annotation\Enum::class, 'target' => \Doctrine\Common\Annotations\Annotation\Target::class, 'attribute' => \Doctrine\Common\Annotations\Annotation\Attribute::class, 'attributes' => \Doctrine\Common\Annotations\Annotation\Attributes::class]);
            // Make sure that annotations from metadata are loaded
            \class_exists(\Doctrine\Common\Annotations\Annotation\Enum::class);
            \class_exists(\Doctrine\Common\Annotations\Annotation\Target::class);
            \class_exists(\Doctrine\Common\Annotations\Annotation\Attribute::class);
            \class_exists(\Doctrine\Common\Annotations\Annotation\Attributes::class);
        }
        $class = new \ReflectionClass($name);
        $docComment = $class->getDocComment();
        // Sets default values for annotation metadata
        $constructor = $class->getConstructor();
        $metadata = ['default_property' => null, 'has_constructor' => $constructor !== null && $constructor->getNumberOfParameters() > 0, 'constructor_args' => [], 'properties' => [], 'property_types' => [], 'attribute_types' => [], 'targets_literal' => null, 'targets' => \Doctrine\Common\Annotations\Annotation\Target::TARGET_ALL, 'is_annotation' => \strpos($docComment, '@Annotation') !== false];
        if (\PHP_VERSION_ID < 80000 && $class->implementsInterface(\Doctrine\Common\Annotations\NamedArgumentConstructorAnnotation::class)) {
            foreach ($constructor->getParameters() as $parameter) {
                $metadata['constructor_args'][$parameter->getName()] = ['position' => $parameter->getPosition(), 'default' => $parameter->isOptional() ? $parameter->getDefaultValue() : null];
            }
        }
        // verify that the class is really meant to be an annotation
        if ($metadata['is_annotation']) {
            self::$metadataParser->setTarget(\Doctrine\Common\Annotations\Annotation\Target::TARGET_CLASS);
            foreach (self::$metadataParser->parse($docComment, 'class @' . $name) as $annotation) {
                if ($annotation instanceof \Doctrine\Common\Annotations\Annotation\Target) {
                    $metadata['targets'] = $annotation->targets;
                    $metadata['targets_literal'] = $annotation->literal;
                    continue;
                }
                if (!$annotation instanceof \Doctrine\Common\Annotations\Annotation\Attributes) {
                    continue;
                }
                foreach ($annotation->value as $attribute) {
                    $this->collectAttributeTypeMetadata($metadata, $attribute);
                }
            }
            // if not has a constructor will inject values into public properties
            if ($metadata['has_constructor'] === false) {
                // collect all public properties
                foreach ($class->getProperties(\ReflectionProperty::IS_PUBLIC) as $property) {
                    $metadata['properties'][$property->name] = $property->name;
                    $propertyComment = $property->getDocComment();
                    if ($propertyComment === false) {
                        continue;
                    }
                    $attribute = new \Doctrine\Common\Annotations\Annotation\Attribute();
                    $attribute->required = \strpos($propertyComment, '@Required') !== false;
                    $attribute->name = $property->name;
                    $attribute->type = \strpos($propertyComment, '@var') !== false && \preg_match('/@var\s+([^\s]+)/', $propertyComment, $matches) ? $matches[1] : 'mixed';
                    $this->collectAttributeTypeMetadata($metadata, $attribute);
                    // checks if the property has @Enum
                    if (\strpos($propertyComment, '@Enum') === false) {
                        continue;
                    }
                    $context = 'property ' . $class->name . '::$' . $property->name;
                    self::$metadataParser->setTarget(\Doctrine\Common\Annotations\Annotation\Target::TARGET_PROPERTY);
                    foreach (self::$metadataParser->parse($propertyComment, $context) as $annotation) {
                        if (!$annotation instanceof \Doctrine\Common\Annotations\Annotation\Enum) {
                            continue;
                        }
                        $metadata['enum'][$property->name]['value'] = $annotation->value;
                        $metadata['enum'][$property->name]['literal'] = !empty($annotation->literal) ? $annotation->literal : $annotation->value;
                    }
                }
                // choose the first property as default property
                $metadata['default_property'] = \reset($metadata['properties']);
            }
        }
        self::$annotationMetadata[$name] = $metadata;
    }
    /**
     * Collects parsing metadata for a given attribute.
     *
     * @param mixed[] $metadata
     */
    private function collectAttributeTypeMetadata(array &$metadata, \Doctrine\Common\Annotations\Annotation\Attribute $attribute): void
    {
        // handle internal type declaration
        $type = self::$typeMap[$attribute->type] ?? $attribute->type;
        // handle the case if the property type is mixed
        if ($type === 'mixed') {
            return;
        }
        // Evaluate type
        $pos = \strpos($type, '<');
        if ($pos !== false) {
            // Checks if the property has array<type>
            $arrayType = \substr($type, $pos + 1, -1);
            $type = 'array';
            if (isset(self::$typeMap[$arrayType])) {
                $arrayType = self::$typeMap[$arrayType];
            }
            $metadata['attribute_types'][$attribute->name]['array_type'] = $arrayType;
        } else {
            // Checks if the property has type[]
            $pos = \strrpos($type, '[');
            if ($pos !== false) {
                $arrayType = \substr($type, 0, $pos);
                $type = 'array';
                if (isset(self::$typeMap[$arrayType])) {
                    $arrayType = self::$typeMap[$arrayType];
                }
                $metadata['attribute_types'][$attribute->name]['array_type'] = $arrayType;
            }
        }
        $metadata['attribute_types'][$attribute->name]['type'] = $type;
        $metadata['attribute_types'][$attribute->name]['value'] = $attribute->type;
        $metadata['attribute_types'][$attribute->name]['required'] = $attribute->required;
    }
    /**
     * Annotations ::= Annotation {[ "*" ]* [Annotation]}*
     *
     * @throws AnnotationException
     * @throws ReflectionException
     *
     * @phpstan-return list<object>
     */
    private function Annotations(): array
    {
        $annotations = [];
        while ($this->lexer->lookahead !== null) {
            if ($this->lexer->lookahead['type'] !== \Doctrine\Common\Annotations\DocLexer::T_AT) {
                $this->lexer->moveNext();
                continue;
            }
            // make sure the @ is preceded by non-catchable pattern
            if ($this->lexer->token !== null && $this->lexer->lookahead['position'] === $this->lexer->token['position'] + \strlen($this->lexer->token['value'])) {
                $this->lexer->moveNext();
                continue;
            }
            // make sure the @ is followed by either a namespace separator, or
            // an identifier token
            $peek = $this->lexer->glimpse();
            if ($peek === null || $peek['type'] !== \Doctrine\Common\Annotations\DocLexer::T_NAMESPACE_SEPARATOR && !\in_array($peek['type'], self::$classIdentifiers, true) || $peek['position'] !== $this->lexer->lookahead['position'] + 1) {
                $this->lexer->moveNext();
                continue;
            }
            $this->isNestedAnnotation = false;
            $annot = $this->Annotation();
            if ($annot === false) {
                continue;
            }
            $annotations[] = $annot;
        }
        return $annotations;
    }
    /**
     * Annotation     ::= "@" AnnotationName MethodCall
     * AnnotationName ::= QualifiedName | SimpleName
     * QualifiedName  ::= NameSpacePart "\" {NameSpacePart "\"}* SimpleName
     * NameSpacePart  ::= identifier | null | false | true
     * SimpleName     ::= identifier | null | false | true
     *
     * @return object|false False if it is not a valid annotation.
     *
     * @throws AnnotationException
     * @throws ReflectionException
     */
    private function Annotation()
    {
        $this->match(\Doctrine\Common\Annotations\DocLexer::T_AT);
        // check if we have an annotation
        $name = $this->Identifier();
        if ($this->lexer->isNextToken(\Doctrine\Common\Annotations\DocLexer::T_MINUS) && $this->lexer->nextTokenIsAdjacent()) {
            // Annotations with dashes, such as "@foo-" or "@foo-bar", are to be discarded
            return false;
        }
        // only process names which are not fully qualified, yet
        // fully qualified names must start with a \
        $originalName = $name;
        if ($name[0] !== '\\') {
            $pos = \strpos($name, '\\');
            $alias = $pos === false ? $name : \substr($name, 0, $pos);
            $found = false;
            $loweredAlias = \strtolower($alias);
            if ($this->namespaces) {
                foreach ($this->namespaces as $namespace) {
                    if ($this->classExists($namespace . '\\' . $name)) {
                        $name = $namespace . '\\' . $name;
                        $found = true;
                        break;
                    }
                }
            } elseif (isset($this->imports[$loweredAlias])) {
                $namespace = \ltrim($this->imports[$loweredAlias], '\\');
                $name = $pos !== false ? $namespace . \substr($name, $pos) : $namespace;
                $found = $this->classExists($name);
            } elseif (!isset($this->ignoredAnnotationNames[$name]) && isset($this->imports['__NAMESPACE__']) && $this->classExists($this->imports['__NAMESPACE__'] . '\\' . $name)) {
                $name = $this->imports['__NAMESPACE__'] . '\\' . $name;
                $found = true;
            } elseif (!isset($this->ignoredAnnotationNames[$name]) && $this->classExists($name)) {
                $found = true;
            }
            if (!$found) {
                if ($this->isIgnoredAnnotation($name)) {
                    return false;
                }
                throw \Doctrine\Common\Annotations\AnnotationException::semanticalError(\sprintf(<<<'EXCEPTION'
The annotation "@%s" in %s was never imported. Did you maybe forget to add a "use" statement for this annotation?
EXCEPTION
, $name, $this->context));
            }
        }
        $name = \ltrim($name, '\\');
        if (!$this->classExists($name)) {
            throw \Doctrine\Common\Annotations\AnnotationException::semanticalError(\sprintf('The annotation "@%s" in %s does not exist, or could not be auto-loaded.', $name, $this->context));
        }
        // at this point, $name contains the fully qualified class name of the
        // annotation, and it is also guaranteed that this class exists, and
        // that it is loaded
        // collects the metadata annotation only if there is not yet
        if (!isset(self::$annotationMetadata[$name])) {
            $this->collectAnnotationMetadata($name);
        }
        // verify that the class is really meant to be an annotation and not just any ordinary class
        if (self::$annotationMetadata[$name]['is_annotation'] === false) {
            if ($this->isIgnoredAnnotation($originalName) || $this->isIgnoredAnnotation($name)) {
                return false;
            }
            throw \Doctrine\Common\Annotations\AnnotationException::semanticalError(\sprintf(<<<'EXCEPTION'
The class "%s" is not annotated with @Annotation.
Are you sure this class can be used as annotation?
If so, then you need to add @Annotation to the _class_ doc comment of "%s".
If it is indeed no annotation, then you need to add @IgnoreAnnotation("%s") to the _class_ doc comment of %s.
EXCEPTION
, $name, $name, $originalName, $this->context));
        }
        //if target is nested annotation
        $target = $this->isNestedAnnotation ? \Doctrine\Common\Annotations\Annotation\Target::TARGET_ANNOTATION : $this->target;
        // Next will be nested
        $this->isNestedAnnotation = true;
        //if annotation does not support current target
        if ((self::$annotationMetadata[$name]['targets'] & $target) === 0 && $target) {
            throw \Doctrine\Common\Annotations\AnnotationException::semanticalError(\sprintf(<<<'EXCEPTION'
Annotation @%s is not allowed to be declared on %s. You may only use this annotation on these code elements: %s.
EXCEPTION
, $originalName, $this->context, self::$annotationMetadata[$name]['targets_literal']));
        }
        $values = $this->MethodCall();
        if (isset(self::$annotationMetadata[$name]['enum'])) {
            // checks all declared attributes
            foreach (self::$annotationMetadata[$name]['enum'] as $property => $enum) {
                // checks if the attribute is a valid enumerator
                if (isset($values[$property]) && !\in_array($values[$property], $enum['value'])) {
                    throw \Doctrine\Common\Annotations\AnnotationException::enumeratorError($property, $name, $this->context, $enum['literal'], $values[$property]);
                }
            }
        }
        // checks all declared attributes
        foreach (self::$annotationMetadata[$name]['attribute_types'] as $property => $type) {
            if ($property === self::$annotationMetadata[$name]['default_property'] && !isset($values[$property]) && isset($values['value'])) {
                $property = 'value';
            }
            // handle a not given attribute or null value
            if (!isset($values[$property])) {
                if ($type['required']) {
                    throw \Doctrine\Common\Annotations\AnnotationException::requiredError($property, $originalName, $this->context, 'a(n) ' . $type['value']);
                }
                continue;
            }
            if ($type['type'] === 'array') {
                // handle the case of a single value
                if (!\is_array($values[$property])) {
                    $values[$property] = [$values[$property]];
                }
                // checks if the attribute has array type declaration, such as "array<string>"
                if (isset($type['array_type'])) {
                    foreach ($values[$property] as $item) {
                        if (\gettype($item) !== $type['array_type'] && !$item instanceof $type['array_type']) {
                            throw \Doctrine\Common\Annotations\AnnotationException::attributeTypeError($property, $originalName, $this->context, 'either a(n) ' . $type['array_type'] . ', or an array of ' . $type['array_type'] . 's', $item);
                        }
                    }
                }
            } elseif (\gettype($values[$property]) !== $type['type'] && !$values[$property] instanceof $type['type']) {
                throw \Doctrine\Common\Annotations\AnnotationException::attributeTypeError($property, $originalName, $this->context, 'a(n) ' . $type['value'], $values[$property]);
            }
        }
        if (\is_subclass_of($name, \Doctrine\Common\Annotations\NamedArgumentConstructorAnnotation::class)) {
            if (\PHP_VERSION_ID >= 80000) {
                return new $name(...$values);
            }
            $positionalValues = [];
            foreach (self::$annotationMetadata[$name]['constructor_args'] as $property => $parameter) {
                $positionalValues[$parameter['position']] = $parameter['default'];
            }
            foreach ($values as $property => $value) {
                if (!isset(self::$annotationMetadata[$name]['constructor_args'][$property])) {
                    throw \Doctrine\Common\Annotations\AnnotationException::creationError(\sprintf(<<<'EXCEPTION'
The annotation @%s declared on %s does not have a property named "%s"
that can be set through its named arguments constructor.
Available named arguments: %s
EXCEPTION
, $originalName, $this->context, $property, \implode(', ', \array_keys(self::$annotationMetadata[$name]['constructor_args']))));
                }
                $positionalValues[self::$annotationMetadata[$name]['constructor_args'][$property]['position']] = $value;
            }
            return new $name(...$positionalValues);
        }
        // check if the annotation expects values via the constructor,
        // or directly injected into public properties
        if (self::$annotationMetadata[$name]['has_constructor'] === true) {
            return new $name($values);
        }
        $instance = new $name();
        foreach ($values as $property => $value) {
            if (!isset(self::$annotationMetadata[$name]['properties'][$property])) {
                if ($property !== 'value') {
                    throw \Doctrine\Common\Annotations\AnnotationException::creationError(\sprintf(<<<'EXCEPTION'
The annotation @%s declared on %s does not have a property named "%s".
Available properties: %s
EXCEPTION
, $originalName, $this->context, $property, \implode(', ', self::$annotationMetadata[$name]['properties'])));
                }
                // handle the case if the property has no annotations
                $property = self::$annotationMetadata[$name]['default_property'];
                if (!$property) {
                    throw \Doctrine\Common\Annotations\AnnotationException::creationError(\sprintf('The annotation @%s declared on %s does not accept any values, but got %s.', $originalName, $this->context, \json_encode($values)));
                }
            }
            $instance->{$property} = $value;
        }
        return $instance;
    }
    /**
     * MethodCall ::= ["(" [Values] ")"]
     *
     * @return mixed[]
     *
     * @throws AnnotationException
     * @throws ReflectionException
     */
    private function MethodCall(): array
    {
        $values = [];
        if (!$this->lexer->isNextToken(\Doctrine\Common\Annotations\DocLexer::T_OPEN_PARENTHESIS)) {
            return $values;
        }
        $this->match(\Doctrine\Common\Annotations\DocLexer::T_OPEN_PARENTHESIS);
        if (!$this->lexer->isNextToken(\Doctrine\Common\Annotations\DocLexer::T_CLOSE_PARENTHESIS)) {
            $values = $this->Values();
        }
        $this->match(\Doctrine\Common\Annotations\DocLexer::T_CLOSE_PARENTHESIS);
        return $values;
    }
    /**
     * Values ::= Array | Value {"," Value}* [","]
     *
     * @return mixed[]
     *
     * @throws AnnotationException
     * @throws ReflectionException
     */
    private function Values(): array
    {
        $values = [$this->Value()];
        while ($this->lexer->isNextToken(\Doctrine\Common\Annotations\DocLexer::T_COMMA)) {
            $this->match(\Doctrine\Common\Annotations\DocLexer::T_COMMA);
            if ($this->lexer->isNextToken(\Doctrine\Common\Annotations\DocLexer::T_CLOSE_PARENTHESIS)) {
                break;
            }
            $token = $this->lexer->lookahead;
            $value = $this->Value();
            if (!\is_object($value) && !\is_array($value)) {
                throw $this->syntaxError('Value', $token);
            }
            $values[] = $value;
        }
        foreach ($values as $k => $value) {
            if (\is_object($value) && $value instanceof \stdClass) {
                $values[$value->name] = $value->value;
            } elseif (!isset($values['value'])) {
                $values['value'] = $value;
            } else {
                if (!\is_array($values['value'])) {
                    $values['value'] = [$values['value']];
                }
                $values['value'][] = $value;
            }
            unset($values[$k]);
        }
        return $values;
    }
    /**
     * Constant ::= integer | string | float | boolean
     *
     * @return mixed
     *
     * @throws AnnotationException
     */
    private function Constant()
    {
        $identifier = $this->Identifier();
        $originalIdentifier = $identifier;
        if (!\defined($identifier) && \strpos($identifier, '::') !== false && $identifier[0] !== '\\') {
            [$className, $const] = \explode('::', $identifier);
            $pos = \strpos($className, '\\');
            $alias = $pos === false ? $className : \substr($className, 0, $pos);
            $found = false;
            $loweredAlias = \strtolower($alias);
            switch (true) {
                case !empty($this->namespaces):
                    foreach ($this->namespaces as $ns) {
                        if (\class_exists($ns . '\\' . $className) || \interface_exists($ns . '\\' . $className)) {
                            $className = $ns . '\\' . $className;
                            $found = true;
                            break;
                        }
                    }
                    break;
                case isset($this->imports[$loweredAlias]):
                    $found = true;
                    $className = $pos !== false ? $this->imports[$loweredAlias] . \substr($className, $pos) : $this->imports[$loweredAlias];
                    break;
                default:
                    if (isset($this->imports['__NAMESPACE__'])) {
                        $ns = $this->imports['__NAMESPACE__'];
                        if (\class_exists($ns . '\\' . $className) || \interface_exists($ns . '\\' . $className)) {
                            $className = $ns . '\\' . $className;
                            $found = true;
                        }
                    }
                    break;
            }
            if ($found) {
                $identifier = $className . '::' . $const;
            }
        }
        /**
         * Checks if identifier ends with ::class and remove the leading backslash if it exists.
         */
        if ($this->identifierEndsWithClassConstant($identifier) && !$this->identifierStartsWithBackslash($identifier)) {
            $resolvedValue = \substr($identifier, 0, $this->getClassConstantPositionInIdentifier($identifier));
            \Rector\DoctrineAnnotationGenerated\DataCollector\ResolvedConstantStaticCollector::collect($originalIdentifier, $resolvedValue);
            return $resolvedValue;
        }
        if ($this->identifierEndsWithClassConstant($identifier) && $this->identifierStartsWithBackslash($identifier)) {
            $resolvedValue = \substr($identifier, 1, $this->getClassConstantPositionInIdentifier($identifier) - 1);
            \Rector\DoctrineAnnotationGenerated\DataCollector\ResolvedConstantStaticCollector::collect($originalIdentifier, $resolvedValue);
            return $resolvedValue;
        }
        if (!\defined($identifier)) {
            throw \Doctrine\Common\Annotations\AnnotationException::semanticalErrorConstants($identifier, $this->context);
        }
        $resolvedValue = \constant($identifier);
        \Rector\DoctrineAnnotationGenerated\DataCollector\ResolvedConstantStaticCollector::collect($originalIdentifier, $resolvedValue);
        return $resolvedValue;
    }
    private function identifierStartsWithBackslash(string $identifier): bool
    {
        return $identifier[0] === '\\';
    }
    private function identifierEndsWithClassConstant(string $identifier): bool
    {
        return $this->getClassConstantPositionInIdentifier($identifier) === \strlen($identifier) - \strlen('::class');
    }
    /**
     * @return int|false
     */
    private function getClassConstantPositionInIdentifier(string $identifier)
    {
        return \stripos($identifier, '::class');
    }
    /**
     * Identifier ::= string
     *
     * @throws AnnotationException
     */
    private function Identifier(): string
    {
        // check if we have an annotation
        if (!$this->lexer->isNextTokenAny(self::$classIdentifiers)) {
            throw $this->syntaxError('namespace separator or identifier');
        }
        $this->lexer->moveNext();
        $className = $this->lexer->token['value'];
        while ($this->lexer->lookahead !== null && $this->lexer->lookahead['position'] === $this->lexer->token['position'] + \strlen($this->lexer->token['value']) && $this->lexer->isNextToken(\Doctrine\Common\Annotations\DocLexer::T_NAMESPACE_SEPARATOR)) {
            $this->match(\Doctrine\Common\Annotations\DocLexer::T_NAMESPACE_SEPARATOR);
            $this->matchAny(self::$classIdentifiers);
            $className .= '\\' . $this->lexer->token['value'];
        }
        return $className;
    }
    /**
     * Value ::= PlainValue | FieldAssignment
     *
     * @return mixed
     *
     * @throws AnnotationException
     * @throws ReflectionException
     */
    private function Value()
    {
        $peek = $this->lexer->glimpse();
        if ($peek['type'] === \Doctrine\Common\Annotations\DocLexer::T_EQUALS) {
            return $this->FieldAssignment();
        }
        return $this->PlainValue();
    }
    /**
     * PlainValue ::= integer | string | float | boolean | Array | Annotation
     *
     * @return mixed
     *
     * @throws AnnotationException
     * @throws ReflectionException
     */
    private function PlainValue()
    {
        if ($this->lexer->isNextToken(\Doctrine\Common\Annotations\DocLexer::T_OPEN_CURLY_BRACES)) {
            return $this->Arrayx();
        }
        if ($this->lexer->isNextToken(\Doctrine\Common\Annotations\DocLexer::T_AT)) {
            return $this->Annotation();
        }
        if ($this->lexer->isNextToken(\Doctrine\Common\Annotations\DocLexer::T_IDENTIFIER)) {
            return $this->Constant();
        }
        switch ($this->lexer->lookahead['type']) {
            case \Doctrine\Common\Annotations\DocLexer::T_STRING:
                $this->match(\Doctrine\Common\Annotations\DocLexer::T_STRING);
                return $this->lexer->token['value'];
            case \Doctrine\Common\Annotations\DocLexer::T_INTEGER:
                $this->match(\Doctrine\Common\Annotations\DocLexer::T_INTEGER);
                return (int) $this->lexer->token['value'];
            case \Doctrine\Common\Annotations\DocLexer::T_FLOAT:
                $this->match(\Doctrine\Common\Annotations\DocLexer::T_FLOAT);
                return (float) $this->lexer->token['value'];
            case \Doctrine\Common\Annotations\DocLexer::T_TRUE:
                $this->match(\Doctrine\Common\Annotations\DocLexer::T_TRUE);
                return true;
            case \Doctrine\Common\Annotations\DocLexer::T_FALSE:
                $this->match(\Doctrine\Common\Annotations\DocLexer::T_FALSE);
                return false;
            case \Doctrine\Common\Annotations\DocLexer::T_NULL:
                $this->match(\Doctrine\Common\Annotations\DocLexer::T_NULL);
                return null;
            default:
                throw $this->syntaxError('PlainValue');
        }
    }
    /**
     * FieldAssignment ::= FieldName "=" PlainValue
     * FieldName ::= identifier
     *
     * @throws AnnotationException
     * @throws ReflectionException
     */
    private function FieldAssignment(): \stdClass
    {
        $this->match(\Doctrine\Common\Annotations\DocLexer::T_IDENTIFIER);
        $fieldName = $this->lexer->token['value'];
        $this->match(\Doctrine\Common\Annotations\DocLexer::T_EQUALS);
        $item = new \stdClass();
        $item->name = $fieldName;
        $item->value = $this->PlainValue();
        return $item;
    }
    /**
     * Array ::= "{" ArrayEntry {"," ArrayEntry}* [","] "}"
     *
     * @return mixed[]
     *
     * @throws AnnotationException
     * @throws ReflectionException
     */
    private function Arrayx(): array
    {
        $array = $values = [];
        $this->match(\Doctrine\Common\Annotations\DocLexer::T_OPEN_CURLY_BRACES);
        // If the array is empty, stop parsing and return.
        if ($this->lexer->isNextToken(\Doctrine\Common\Annotations\DocLexer::T_CLOSE_CURLY_BRACES)) {
            $this->match(\Doctrine\Common\Annotations\DocLexer::T_CLOSE_CURLY_BRACES);
            return $array;
        }
        $values[] = $this->ArrayEntry();
        while ($this->lexer->isNextToken(\Doctrine\Common\Annotations\DocLexer::T_COMMA)) {
            $this->match(\Doctrine\Common\Annotations\DocLexer::T_COMMA);
            // optional trailing comma
            if ($this->lexer->isNextToken(\Doctrine\Common\Annotations\DocLexer::T_CLOSE_CURLY_BRACES)) {
                break;
            }
            $values[] = $this->ArrayEntry();
        }
        $this->match(\Doctrine\Common\Annotations\DocLexer::T_CLOSE_CURLY_BRACES);
        foreach ($values as $value) {
            [$key, $val] = $value;
            if ($key !== null) {
                $array[$key] = $val;
            } else {
                $array[] = $val;
            }
        }
        return $array;
    }
    /**
     * ArrayEntry ::= Value | KeyValuePair
     * KeyValuePair ::= Key ("=" | ":") PlainValue | Constant
     * Key ::= string | integer | Constant
     *
     * @throws AnnotationException
     * @throws ReflectionException
     *
     * @phpstan-return array{mixed, mixed}
     */
    private function ArrayEntry(): array
    {
        $peek = $this->lexer->glimpse();
        if ($peek['type'] === \Doctrine\Common\Annotations\DocLexer::T_EQUALS || $peek['type'] === \Doctrine\Common\Annotations\DocLexer::T_COLON) {
            if ($this->lexer->isNextToken(\Doctrine\Common\Annotations\DocLexer::T_IDENTIFIER)) {
                $key = $this->Constant();
            } else {
                $this->matchAny([\Doctrine\Common\Annotations\DocLexer::T_INTEGER, \Doctrine\Common\Annotations\DocLexer::T_STRING]);
                $key = $this->lexer->token['value'];
            }
            $this->matchAny([\Doctrine\Common\Annotations\DocLexer::T_EQUALS, \Doctrine\Common\Annotations\DocLexer::T_COLON]);
            return [$key, $this->PlainValue()];
        }
        return [null, $this->Value()];
    }
    /**
     * Checks whether the given $name matches any ignored annotation name or namespace
     */
    private function isIgnoredAnnotation(string $name): bool
    {
        if ($this->ignoreNotImportedAnnotations || isset($this->ignoredAnnotationNames[$name])) {
            return true;
        }
        foreach (\array_keys($this->ignoredAnnotationNamespaces) as $ignoredAnnotationNamespace) {
            $ignoredAnnotationNamespace = \rtrim($ignoredAnnotationNamespace, '\\') . '\\';
            if (\stripos(\rtrim($name, '\\') . '\\', $ignoredAnnotationNamespace) === 0) {
                return true;
            }
        }
        return false;
    }
}
