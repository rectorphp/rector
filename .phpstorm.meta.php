<?php declare(strict_types=1);

// see https://confluence.jetbrains.com/display/PhpStorm/PhpStorm+Advanced+Metadata
namespace PHPSTORM_META;

// $container->get(Type::class) → instance of "Type"
override(\Psr\Container\ContainerInterface::get(0), type(0));

// PhpStorm 2019.1 - add argument autocomplete
// https://blog.jetbrains.com/phpstorm/2019/02/new-phpstorm-meta-php-features/
expectedArguments(
    \PhpParser\Node::getAttribute(),
    0,
    \Rector\NodeTypeResolver\Node\Attribute::SCOPE,
    \Rector\NodeTypeResolver\Node\Attribute::CLASS_NODE,
    \Rector\NodeTypeResolver\Node\Attribute::CLASS_NAME,
    \Rector\NodeTypeResolver\Node\Attribute::FILE_INFO,
    \Rector\NodeTypeResolver\Node\Attribute::METHOD_NAME,
    \Rector\NodeTypeResolver\Node\Attribute::METHOD_NODE,
    \Rector\NodeTypeResolver\Node\Attribute::NAMESPACE_NAME,
    \Rector\NodeTypeResolver\Node\Attribute::NAMESPACE_NODE,
    \Rector\NodeTypeResolver\Node\Attribute::PARENT_NODE,
    \Rector\NodeTypeResolver\Node\Attribute::NEXT_NODE,
    \Rector\NodeTypeResolver\Node\Attribute::PREVIOUS_NODE,
    \Rector\NodeTypeResolver\Node\Attribute::CURRENT_EXPRESSION,
    \Rector\NodeTypeResolver\Node\Attribute::PREVIOUS_EXPRESSION,
    \Rector\NodeTypeResolver\Node\Attribute::USE_NODES,
    \Rector\NodeTypeResolver\Node\Attribute::START_TOKEN_POSITION
);

expectedArguments(
    \PhpParser\Node::setAttribute(),
    0,
    \Rector\NodeTypeResolver\Node\Attribute::SCOPE,
    \Rector\NodeTypeResolver\Node\Attribute::CLASS_NODE,
    \Rector\NodeTypeResolver\Node\Attribute::CLASS_NAME,
    \Rector\NodeTypeResolver\Node\Attribute::FILE_INFO,
    \Rector\NodeTypeResolver\Node\Attribute::METHOD_NAME,
    \Rector\NodeTypeResolver\Node\Attribute::METHOD_NODE,
    \Rector\NodeTypeResolver\Node\Attribute::NAMESPACE_NAME,
    \Rector\NodeTypeResolver\Node\Attribute::NAMESPACE_NODE,
    \Rector\NodeTypeResolver\Node\Attribute::PARENT_NODE,
    \Rector\NodeTypeResolver\Node\Attribute::NEXT_NODE,
    \Rector\NodeTypeResolver\Node\Attribute::PREVIOUS_NODE,
    \Rector\NodeTypeResolver\Node\Attribute::CURRENT_EXPRESSION,
    \Rector\NodeTypeResolver\Node\Attribute::PREVIOUS_EXPRESSION,
    \Rector\NodeTypeResolver\Node\Attribute::USE_NODES,
    \Rector\NodeTypeResolver\Node\Attribute::START_TOKEN_POSITION
);
