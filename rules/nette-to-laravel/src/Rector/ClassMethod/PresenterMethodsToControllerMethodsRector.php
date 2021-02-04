<?php

namespace App\Rector\Rules;

use App\Requests\Request;
use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\NullableType;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Core\PhpParser\Node\NodeFactory;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Sensio\TypeDeclaration\ReturnTypeDeclarationUpdater;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use function assert;
use function end;
use function reset;
use function str_replace;
use function strpos;

final class PresenterMethodsToControllerMethodsRector extends AbstractRector
{
	/**
	 * @see https://regex101.com/r/f97wwM/1
	 */
	private const COMMON_PUBLIC_METHOD_CONTROLLER_REGEX = '#^(render|action|handle)#';

	/**
	 * @see https://regex101.com/r/FXhI9M/1
	 */
	private const CONTROLLER_PRESENTER_SUFFIX_REGEX = '#(Controller|Presenter)$#';
	private const NAMESPACE = '#(Presenters)#';
	private const BASE_REQUEST_CLASS = "App\\Requests\\Request";
	private const RETURN_METHOD = "response";
	private const RESPONSE_CLASS = "\Symfony\Component\HttpFoundation\Response";

	/** @var NodeFactory */
	protected $nodeFactory;

	/** @var ReturnTypeDeclarationUpdater */
	private $returnTypeDeclarationUpdater;

	function __construct(NodeFactory $nodeFactory, ReturnTypeDeclarationUpdater $returnTypeDeclarationUpdater)
	{
		$this->nodeFactory = $nodeFactory;
		$this->returnTypeDeclarationUpdater = $returnTypeDeclarationUpdater;
	}

	/**
	 * @return string[]
	 */
	public function getNodeTypes(): array
	{
		return [ClassMethod::class];
	}

	public function getRuleDefinition(): RuleDefinition
	{
		return new RuleDefinition(
			'Move action/render methods parameters inside', [
			new CodeSample(
				<<<'CODE_SAMPLE'
final class SomePresenter
{
    public function actionDetail(int $id, ?string $date = null)
    {

    }
}
CODE_SAMPLE
				,
				<<<'CODE_SAMPLE'
final class SomePresenter
{
    public function actionDetail(Request $request) : Response
    {
        $id = $request->input("id");
        $date = $request->input("date");

        return $this->response()
    }
}
CODE_SAMPLE,
			),
		],
		);
	}

	/**
	 * @param ClassMethod $node
	 */
	public function refactor(Node $node): ?Node
	{
		if ($this->shouldSkip($node)) {
			return null;
		}

		return $this->migrateParamsIntoMethod($node);
	}

	private function shouldSkip(ClassMethod $classMethod): bool
	{
		$namespace = $classMethod->getAttribute(AttributeKey::NAMESPACE_NAME);

		if (!Strings::match($namespace, self::NAMESPACE)) {
			return true;
		}

		$classLike = $classMethod->getAttribute(AttributeKey::CLASS_NODE);

		if (!$classLike instanceof Class_) {
			return true;
		}

		if ($classLike->isAbstract()) {
			return true;
		}

		if (!$this->isControllerAction($classLike, $classMethod)) {
			return true;
		}

		return false;
	}

	private function isControllerAction(Class_ $class, ClassMethod $classMethod): bool
	{
		$className = $class->getAttribute(AttributeKey::CLASS_NAME);

		if ($className === null) {
			return false;
		}

		if (!Strings::match($className, self::CONTROLLER_PRESENTER_SUFFIX_REGEX)) {
			return false;
		}

		$classMethodName = $this->getName($classMethod);

		if ((bool)Strings::match($classMethodName, self::COMMON_PUBLIC_METHOD_CONTROLLER_REGEX)) {
			return true;
		}
		return false;
	}

	/**
	 * @param ClassMethod $classMethod
	 */
	private function migrateParamsIntoMethod(ClassMethod $classMethod): ?ClassMethod
	{
		$stmts = $classMethod->getStmts();

		// first line of function
		$first = reset($stmts);

		if (!$first) {
			return null;
		}

		$last = end($stmts);

		if (!$last) {
			return null;
		}

		/** @var Class_ $class */
		$class = $classMethod->getAttribute(AttributeKey::CLASS_NODE);
		$class->name = new Node\Identifier(str_replace("Presenter", "Controller", $class->name));

		$params = $classMethod->getParams();
		$firstType = $params[0]->type ?? null;

		$name = $firstType->name ?? null;
		if ($firstType && !isset($firstType->name)) {
			if ($firstType instanceof FullyQualified) {
				$name = (string)$firstType;
			} elseif ($firstType instanceof Node\NullableType) {
				$name = (string)$firstType->type->name;
			} else {
				$name = (string)$firstType;
			}
		}

		if (!$firstType || !$name || strpos(self::BASE_REQUEST_CLASS, $name) === false) {
			$this->migrateFromParams($classMethod, $first);
			$this->setRequestToParam($classMethod);
		}


		$this->returnResponse($last);

		$nodeReturnTypeName = $this->getName(
			$classMethod->returnType instanceof NullableType ? $classMethod->returnType->type : $classMethod->returnType
		);
		if (strpos($nodeReturnTypeName, "Response") === false) {
			$this->returnTypeDeclarationUpdater->updateClassMethod($classMethod, self::RESPONSE_CLASS);

			//$classMethod->returnType =  new Node\Identifier(self::RESPONSE_CLASS);
		}

		return $classMethod;
	}

	private function migrateFromParams(ClassMethod $classMethod, Node\Stmt $first)
	{
		$args = [];

		foreach ($classMethod->getParams() as $param) {
			assert($param instanceof Param);
			// function actionTest(int $id, int $id2)
			$args[] = $this->getName($param);
		}

		foreach ($args as $var) {
			// $request->input('id');
			$methodCall = $this->createMethodCall(
				"request",
				"input",
				[
					$this->createArg($var),
				],
			);

			$assign = new Assign(new Variable($var), $methodCall);
			$this->addNodeBeforeNode($assign, $first);
		}
	}

	private function setRequestToParam(ClassMethod $classMethod)
	{
		$classMethod->params = [
			new Param(new Variable("request"), null, new FullyQualified(self::BASE_REQUEST_CLASS)),
		];
	}

	/**
	 * Puts return $this->response() at the end.
	 *
	 * @param Node $last
	 */
	private function returnResponse(Node $last)
	{
		if ($last instanceof Node\Stmt\Return_) {
			assert($last instanceof Node\Stmt\Return_);


			/// @todo maybe better use betterNodeFinder? Is it necessary?
			///$lastReturn = $this->betterNodeFinder->findLastInstanceOf((array) $classMethod->stmts, Return_::class);

			if ($last->expr instanceof MethodCall && $this->getName($last->expr->name) === self::RETURN_METHOD) {
				return;
			}
		}

		$methodCall = new MethodCall(new Variable('this'), 'response', []);
		$return = new Node\Stmt\Return_($methodCall);
		$this->addNodeAfterNode($return, $last);
	}
}
