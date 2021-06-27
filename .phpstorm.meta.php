<?php

declare(strict_types=1);

// see https://confluence.jetbrains.com/display/PhpStorm/PhpStorm+Advanced+Metadata
namespace PHPSTORM_META;

// $container->get(Type::class) → instance of "Type"
use Rector\BetterPhpDocParser\ValueObject\PhpDocAttributeKey;

override(\Psr\Container\ContainerInterface::get(0), type(0));

expectedArguments(
    \PHPStan\PhpDocParser\Ast\Node::getAttribute(),
    0,
    PhpDocAttributeKey::START_AND_END,
    PhpDocAttributeKey::LAST_PHP_DOC_TOKEN_POSITION,
    PhpDocAttributeKey::PARENT,
    PhpDocAttributeKey::ORIG_NODE,
    PhpDocAttributeKey::RESOLVED_CLASS,
);

expectedArguments(
    \PHPStan\PhpDocParser\Ast\NodeAttributes::getAttribute(),
    0,
    PhpDocAttributeKey::START_AND_END,
    PhpDocAttributeKey::LAST_PHP_DOC_TOKEN_POSITION,
    PhpDocAttributeKey::PARENT,
    PhpDocAttributeKey::ORIG_NODE,
    PhpDocAttributeKey::RESOLVED_CLASS,
);

expectedArguments(
    \PHPStan\PhpDocParser\Ast\Node::hasAttribute(),
    0,
    PhpDocAttributeKey::START_AND_END,
    PhpDocAttributeKey::LAST_PHP_DOC_TOKEN_POSITION,
    PhpDocAttributeKey::PARENT,
    PhpDocAttributeKey::ORIG_NODE,
    PhpDocAttributeKey::RESOLVED_CLASS,
);


// PhpStorm 2019.1 - add argument autocomplete
// https://blog.jetbrains.com/phpstorm/2019/02/new-phpstorm-meta-php-features/
expectedArguments(
    \PhpParser\Node::getAttribute(),
    0,
    \Rector\NodeTypeResolver\Node\AttributeKey::SCOPE,
    \Rector\NodeTypeResolver\Node\AttributeKey::CLASS_NODE,
    \Rector\NodeTypeResolver\Node\AttributeKey::CLASS_NAME,
    \Rector\NodeTypeResolver\Node\AttributeKey::METHOD_NODE,
    \Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE,
    \Rector\NodeTypeResolver\Node\AttributeKey::NEXT_NODE,
    \Rector\NodeTypeResolver\Node\AttributeKey::PREVIOUS_NODE,
    \Rector\NodeTypeResolver\Node\AttributeKey::CURRENT_STATEMENT,
    \Rector\NodeTypeResolver\Node\AttributeKey::PREVIOUS_STATEMENT,
    \Rector\NodeTypeResolver\Node\AttributeKey::USE_NODES,
    \Rector\NodeTypeResolver\Node\AttributeKey::START_TOKEN_POSITION,
    \Rector\NodeTypeResolver\Node\AttributeKey::ORIGINAL_NODE,
    \Rector\NodeTypeResolver\Node\AttributeKey::IS_UNREACHABLE,
    \Rector\NodeTypeResolver\Node\AttributeKey::PHP_DOC_INFO,
    \Rector\NodeTypeResolver\Node\AttributeKey::KIND,
    \Rector\NodeTypeResolver\Node\AttributeKey::IS_REGULAR_PATTERN,
    \Rector\NodeTypeResolver\Node\AttributeKey::ORIGINAL_NAME,
    \Rector\NodeTypeResolver\Node\AttributeKey::COMMENTS,
    \Rector\NodeTypeResolver\Node\AttributeKey::VIRTUAL_NODE,
    \Rector\NodeTypeResolver\Node\AttributeKey::PARAMETER_POSITION,
    \Rector\NodeTypeResolver\Node\AttributeKey::ARGUMENT_POSITION,
);

expectedArguments(
    \PhpParser\Node::setAttribute(),
    0,
    \Rector\NodeTypeResolver\Node\AttributeKey::SCOPE,
    \Rector\NodeTypeResolver\Node\AttributeKey::CLASS_NODE,
    \Rector\NodeTypeResolver\Node\AttributeKey::CLASS_NAME,
    \Rector\NodeTypeResolver\Node\AttributeKey::METHOD_NODE,
    \Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE,
    \Rector\NodeTypeResolver\Node\AttributeKey::NEXT_NODE,
    \Rector\NodeTypeResolver\Node\AttributeKey::PREVIOUS_NODE,
    \Rector\NodeTypeResolver\Node\AttributeKey::CURRENT_STATEMENT,
    \Rector\NodeTypeResolver\Node\AttributeKey::PREVIOUS_STATEMENT,
    \Rector\NodeTypeResolver\Node\AttributeKey::USE_NODES,
    \Rector\NodeTypeResolver\Node\AttributeKey::START_TOKEN_POSITION,
    \Rector\NodeTypeResolver\Node\AttributeKey::ORIGINAL_NODE,
    \Rector\NodeTypeResolver\Node\AttributeKey::IS_UNREACHABLE,
    \Rector\NodeTypeResolver\Node\AttributeKey::PHP_DOC_INFO,
    \Rector\NodeTypeResolver\Node\AttributeKey::KIND,
    \Rector\NodeTypeResolver\Node\AttributeKey::IS_REGULAR_PATTERN,
    \Rector\NodeTypeResolver\Node\AttributeKey::ORIGINAL_NAME,
    \Rector\NodeTypeResolver\Node\AttributeKey::COMMENTS,
    \Rector\NodeTypeResolver\Node\AttributeKey::VIRTUAL_NODE,
    \Rector\NodeTypeResolver\Node\AttributeKey::PARAMETER_POSITION,
    \Rector\NodeTypeResolver\Node\AttributeKey::ARGUMENT_POSITION,
);

expectedArguments(
    \Rector\Core\Rector\AbstractRector::isAtLeastPhpVersion(),
    0,
    \Rector\Core\ValueObject\PhpVersionFeature::DIR_CONSTANT,
    \Rector\Core\ValueObject\PhpVersionFeature::ELVIS_OPERATOR,
    \Rector\Core\ValueObject\PhpVersionFeature::CLASSNAME_CONSTANT,
    \Rector\Core\ValueObject\PhpVersionFeature::EXP_OPERATOR,
    \Rector\Core\ValueObject\PhpVersionFeature::SCALAR_TYPES,
    \Rector\Core\ValueObject\PhpVersionFeature::NULL_COALESCE,
    \Rector\Core\ValueObject\PhpVersionFeature::SPACESHIP,
    \Rector\Core\ValueObject\PhpVersionFeature::DIRNAME_LEVELS,
    \Rector\Core\ValueObject\PhpVersionFeature::CSPRNG_FUNCTIONS,
    \Rector\Core\ValueObject\PhpVersionFeature::THROWABLE_TYPE,
    \Rector\Core\ValueObject\PhpVersionFeature::ITERABLE_TYPE,
    \Rector\Core\ValueObject\PhpVersionFeature::VOID_TYPE,
    \Rector\Core\ValueObject\PhpVersionFeature::CONSTANT_VISIBILITY,
    \Rector\Core\ValueObject\PhpVersionFeature::ARRAY_DESTRUCT,
    \Rector\Core\ValueObject\PhpVersionFeature::MULTI_EXCEPTION_CATCH,
    \Rector\Core\ValueObject\PhpVersionFeature::OBJECT_TYPE,
    \Rector\Core\ValueObject\PhpVersionFeature::IS_COUNTABLE,
    \Rector\Core\ValueObject\PhpVersionFeature::ARRAY_KEY_FIRST_LAST,
    \Rector\Core\ValueObject\PhpVersionFeature::JSON_EXCEPTION,
    \Rector\Core\ValueObject\PhpVersionFeature::SETCOOKIE_ACCEPT_ARRAY_OPTIONS,
    \Rector\Core\ValueObject\PhpVersionFeature::ARROW_FUNCTION,
    \Rector\Core\ValueObject\PhpVersionFeature::LITERAL_SEPARATOR,
    \Rector\Core\ValueObject\PhpVersionFeature::NULL_COALESCE_ASSIGN,
    \Rector\Core\ValueObject\PhpVersionFeature::TYPED_PROPERTIES,
    \Rector\Core\ValueObject\PhpVersionFeature::COVARIANT_RETURN,
    \Rector\Core\ValueObject\PhpVersionFeature::ARRAY_SPREAD,
    \Rector\Core\ValueObject\PhpVersionFeature::UNION_TYPES,
);
