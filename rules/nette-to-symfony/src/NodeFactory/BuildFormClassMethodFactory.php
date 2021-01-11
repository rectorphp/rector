<?php

declare(strict_types=1);

namespace Rector\NetteToSymfony\NodeFactory;

use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Core\PhpParser\Node\NodeFactory;

final class BuildFormClassMethodFactory
{
    /**
     * @var NodeFactory
     */
    private $nodeFactory;

    public function __construct(NodeFactory $nodeFactory)
    {
        $this->nodeFactory = $nodeFactory;
    }

    public function create(Variable $formBuilderVariable): ClassMethod
    {
        $buildFormClassMethod = $this->nodeFactory->createPublicMethod('buildForm');
        $buildFormClassMethod->params[] = new Param($formBuilderVariable, null, new FullyQualified(
            'Symfony\Component\Form\FormBuilderInterface'
        ));
        $buildFormClassMethod->params[] = new Param(new Variable('options'), null, new Identifier('array'));

        return $buildFormClassMethod;
    }
}
