<?php declare(strict_types=1);

namespace IndependentSingleActionPresenter\Application;

use Nette;
use Nette\Application\Application;
use Nette\Application\IPresenter;
use Nette\Application\IPresenterFactory;
use Nette\Application\IRouter;
use Nette\Application\Request;
use Nette\Application\AbortException;
use Nette\Application\ApplicationException;
use Nette\Application\BadRequestException;
use Nette\Application\InvalidPresenterException;
use Nette\Application\Responses;
use Nette\Application\UI;
use Nette\Http\IRequest;
use Nette\Http\IResponse;


/**
 * Front Controller.
 */
final class CallablePresenterAwareApplication extends Application
{
	use Nette\SmartObject;

	/**
	 * @var int
	 */
	public static $maxLoop = 20;

	/**
	 * @var bool enable fault barrier?
	 */
	public $catchExceptions;

	/**
	 * @var string
	 */
	public $errorPresenter;

	/**
	 * @var callable[]  function (Application $sender); Occurs before the application loa
	 * ds presenter */
	public $onStartup;

	/**
	 * @var callable[]  function (Application $sender, \Exception|\Throwable $e = NULL); Occu
	 * rs before the application shuts down */
	public $onShutdown;

	/**
	 * @var callable[]  function (Application $sender, Request $request);
	 * Occurs when a new request is received */
	public $onRequest;

	/**
	 * @var callable[]  function (Application $sender, Presenter $presenter);
	 * Occurs when a presenter is created */
	public $onPresenter;

	/**
	 * @var callable[]  function (Application $sender, IResponse $response);
	 * Occurs when a new response is ready for dispatch */
	public $onResponse;

	/**
	 * @var callable[]  function (Application $sender, \Exception|\Throwable $e);
	 * Occurs when an unhandled exception occurs in the application */
	public $onError;

	/**
	 * @var Request[]
	 */
	private $requests = [];

	/**
	 * @var IPresenter|callable|NULL
	 */
	private $presenter;

	/**
	 * @var IRequest
	 */
	private $httpRequest;

	/**
	 * @var IResponse
	 */
	private $httpResponse;

	/**
	 * @var IPresenterFactory
	 */
	private $presenterFactory;

	/**
	 * @var IRouter
	 */
	private $router;


	public function __construct(
		IPresenterFactory $presenterFactory,
		IRouter $router,
		IRequest $httpRequest,
		IResponse $httpResponse
	) {
		$this->httpRequest = $httpRequest;
		$this->httpResponse = $httpResponse;
		$this->presenterFactory = $presenterFactory;
		$this->router = $router;
	}


	public function run(): void
	{
		try {
			$this->onStartup($this); // use event-dispatcher instead
			$this->processRequest($this->createInitialRequest());
			$this->onShutdown($this); // use event-dispatcher instead

		} catch (\Throwable|\Exception $e) {
		}
		if (isset($e)) {
			$this->onError($this, $e); // use event-dispatcher instead
			if ($this->catchExceptions && $this->errorPresenter) {
				try {
					$this->processException($e);
					$this->onShutdown($this, $e); // use event-dispatcher instead
					return;

				} catch (\Throwable|\Exception $e) {
					$this->onError($this, $e); // use event-dispatcher instead
				}
			}
			$this->onShutdown($this, $e); // use event-dispatcher instead
			throw $e;
		}
	}


	public function createInitialRequest(): Request
	{
		$request = $this->router->match($this->httpRequest);
		if (!$request instanceof Request) {
			throw new BadRequestException('No route for HTTP request.');
		}
		return $request;
	}


	public function processRequest(Request $request): void
	{
		process:
		if (count($this->requests) > self::$maxLoop) {
			throw new ApplicationException('Too many loops detected in application life cycle.');
		}

		$this->requests[] = $request;
		$this->onRequest($this, $request); // use event-dispatcher instead

		if (!$request->isMethod($request::FORWARD) && !strcasecmp($request->getPresenterName(), $this->errorPresenter)) {
			throw new BadRequestException('Invalid request. Presenter is not achievable.');
		}

		try {
			$this->presenter = $this->presenterFactory->createPresenter($request->getPresenterName());
		} catch (InvalidPresenterException $e) {
			throw count($this->requests) > 1 ? $e : new BadRequestException($e->getMessage(), 0, $e);
		}
		$this->onPresenter($this, $this->presenter); // use event-dispatcher instead

		if (is_callable($this->presenter)) {
			$presenter = $this->presenter;
			$response = $presenter(clone $request);
		} else {
			$response = $this->presenter->run(clone $request);
		}

		if ($response instanceof Responses\ForwardResponse) {
			$request = $response->getRequest();
			goto process;

		} elseif ($response) {
			$this->onResponse($this, $response); // use event-dispatcher instead
			$response->send($this->httpRequest, $this->httpResponse);
		}
	}


	/**
	 * @param \Exception|\Throwable $e
	 */
	public function processException($e): void
	{
		if (!$e instanceof BadRequestException && $this->httpResponse instanceof Nette\Http\Response) {
			$this->httpResponse->warnOnBuffer = FALSE;
		}
		if (!$this->httpResponse->isSent()) {
			$this->httpResponse->setCode($e instanceof BadRequestException ? ($e->getHttpCode() ?: 404) : 500);
		}

		$args = ['exception' => $e, 'request' => end($this->requests) ?: NULL];
		if ($this->presenter instanceof UI\Presenter) {
			try {
				$this->presenter->forward(":$this->errorPresenter:", $args);
			} catch (AbortException $foo) {
				$this->processRequest($this->presenter->getLastCreatedRequest());
			}
		} else {
			$this->processRequest(new Request($this->errorPresenter, Request::FORWARD, $args));
		}
	}


	/**
	 * Returns all processed requests.
	 * @return Request[]
	 */
	public function getRequests(): array
	{
		return $this->requests;
	}


	public function getPresenter(): ?IPresenter
	{
		return $this->presenter;
	}

}