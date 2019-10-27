<?php

declare(strict_types=1);

// see https://confluence.jetbrains.com/display/PhpStorm/PhpStorm+Advanced+Metadata
namespace PHPSTORM_META;

// $container->get(Type::class) → instance of "Type"
override(\Psr\Container\ContainerInterface::get(0), type(0));

// $propertyPhpDocInfo->getByType(Type::class) → instance of "Type"|null
# inspired at: https://github.com/Ocramius/phpunit/blob/2894f1e5eb2cd88708fdba608718e5b6a07391aa/.phpstorm.meta.php#L4-L9
override(
    \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo::getByType(0),
    map(['@&null'])
);

// PhpStorm 2019.1 - add argument autocomplete
// https://blog.jetbrains.com/phpstorm/2019/02/new-phpstorm-meta-php-features/
expectedArguments(
    \PhpParser\Node::getAttribute(),
    0,
    \Rector\NodeTypeResolver\Node\AttributeKey::SCOPE,
    \Rector\NodeTypeResolver\Node\AttributeKey::CLASS_NODE,
    \Rector\NodeTypeResolver\Node\AttributeKey::CLASS_NAME,
    \Rector\NodeTypeResolver\Node\AttributeKey::FILE_INFO,
    \Rector\NodeTypeResolver\Node\AttributeKey::METHOD_NAME,
    \Rector\NodeTypeResolver\Node\AttributeKey::METHOD_NODE,
    \Rector\NodeTypeResolver\Node\AttributeKey::NAMESPACE_NAME,
    \Rector\NodeTypeResolver\Node\AttributeKey::NAMESPACE_NODE,
    \Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE,
    \Rector\NodeTypeResolver\Node\AttributeKey::PARENT_CLASS_NAME,
    \Rector\NodeTypeResolver\Node\AttributeKey::NEXT_NODE,
    \Rector\NodeTypeResolver\Node\AttributeKey::PREVIOUS_NODE,
    \Rector\NodeTypeResolver\Node\AttributeKey::CURRENT_STATEMENT,
    \Rector\NodeTypeResolver\Node\AttributeKey::PREVIOUS_STATEMENT,
    \Rector\NodeTypeResolver\Node\AttributeKey::USE_NODES,
    \Rector\NodeTypeResolver\Node\AttributeKey::START_TOKEN_POSITION,
    \Rector\NodeTypeResolver\Node\AttributeKey::ORIGINAL_NODE,
    \Rector\NodeTypeResolver\Node\AttributeKey::IS_UNREACHABLE,
);

expectedArguments(
    \PhpParser\Node::setAttribute(),
    0,
    \Rector\NodeTypeResolver\Node\AttributeKey::SCOPE,
    \Rector\NodeTypeResolver\Node\AttributeKey::CLASS_NODE,
    \Rector\NodeTypeResolver\Node\AttributeKey::CLASS_NAME,
    \Rector\NodeTypeResolver\Node\AttributeKey::FILE_INFO,
    \Rector\NodeTypeResolver\Node\AttributeKey::METHOD_NAME,
    \Rector\NodeTypeResolver\Node\AttributeKey::METHOD_NODE,
    \Rector\NodeTypeResolver\Node\AttributeKey::NAMESPACE_NAME,
    \Rector\NodeTypeResolver\Node\AttributeKey::NAMESPACE_NODE,
    \Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE,
    \Rector\NodeTypeResolver\Node\AttributeKey::PARENT_CLASS_NAME,
    \Rector\NodeTypeResolver\Node\AttributeKey::NEXT_NODE,
    \Rector\NodeTypeResolver\Node\AttributeKey::PREVIOUS_NODE,
    \Rector\NodeTypeResolver\Node\AttributeKey::CURRENT_STATEMENT,
    \Rector\NodeTypeResolver\Node\AttributeKey::PREVIOUS_STATEMENT,
    \Rector\NodeTypeResolver\Node\AttributeKey::USE_NODES,
    \Rector\NodeTypeResolver\Node\AttributeKey::START_TOKEN_POSITION,
    \Rector\NodeTypeResolver\Node\AttributeKey::ORIGINAL_NODE,
    \Rector\NodeTypeResolver\Node\AttributeKey::IS_UNREACHABLE,
);
