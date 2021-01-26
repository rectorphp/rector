<?php

namespace App\Rector\Rules;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Core\PhpParser\Node\NodeFactory;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use function assert;

final class PresenterMethodsToControllerMethodsRector extends AbstractRector
{
	/**
	 * @see https://regex101.com/r/f97wwM/1
	 */
	private const COMMON_PUBLIC_METHOD_CONTROLLER_REGEX = '#^(render|action)#';

	/**
	 * @see https://regex101.com/r/FXhI9M/1
	 */
	private const CONTROLLER_PRESENTER_SUFFIX_REGEX = '#(Controller|Presenter)$#';
	private const BASE_REQUEST_CLASS = "\\App\\Requests\\Request";
	private const NAMESPACE = '#(ApiAdminModule|ApiModule)$#';

	/** @var NodeFactory */
	protected $nodeFactory;

	function __construct(NodeFactory $nodeFactory)
	{
		$this->nodeFactory = $nodeFactory;
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
    public function actionDetail(Request $request)
    {
        $id = $request->input("id");
        $date = $request->input("date");
    }
}
CODE_SAMPLE,
			),
		],
		);
	}

	/**
	 * @return string[]
	 */
	public function getNodeTypes(): array
	{
		return [ClassMethod::class];
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

		$this->migrateFromParams($classMethod, $first);
		$this->setRequestToParam($classMethod);
		$this->returnResponse($last);

		return $classMethod;
	}

	/**
	 * Puts return $this->response() at the end.
	 *
	 * @param Node $last
	 */
	private function returnResponse(Node $last) {
		$methodCall = new MethodCall(new Variable('this'), 'response', []);
		$return = new Node\Stmt\Return_($methodCall);
		$this->addNodeAfterNode($return, $last);
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
}
