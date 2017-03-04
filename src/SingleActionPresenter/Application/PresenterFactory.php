<?php declare(strict_types=1);

namespace SingleActionPresenter\Application;

use Nette;
use Nette\Application\InvalidPresenterException;
use Nette\Application\IPresenter;
use Nette\Application\IPresenterFactory;
use Nette\DI\Container;


/**
 * Default presenter loader.
 */
final class PresenterFactory implements IPresenterFactory
{
	use Nette\SmartObject;

	/** @var array[] of module => splited mask */
	private $mapping = [
		'*' => ['', '*Module\\', '*Presenter'],
		'Nette' => ['NetteModule\\', '*\\', '*Presenter'],
	];

	/** @var array */
	private $cache = [];

	/**
	 * @var Container
	 */
	private $container;


	public function __construct(Container $container)
	{
		$this->container = $container;
	}


	/**
	 * @param string $name
	 * @return IPresenter|callable|object
	 */
	public function createPresenter($name)
	{
		$presenterClass = $this->getPresenterClass($name);

		return $this->container->createInstance($presenterClass);
	}


	/**
	 * Generates and checks presenter class name.
	 * @param string $name presenter name
	 */
	public function getPresenterClass(&$name): string
	{
		if (isset($this->cache[$name])) {
			return $this->cache[$name];
		}

		if (!is_string($name) || !Nette\Utils\Strings::match($name, '#^[a-zA-Z\x7f-\xff][a-zA-Z0-9\x7f-\xff:]*\z#')) {
			throw new InvalidPresenterException("Presenter name must be alphanumeric string, '$name' is invalid.");
		}

		$class = $this->formatPresenterClass($name);
		if (!class_exists($class)) {
			throw new InvalidPresenterException("Cannot load presenter '$name', class '$class' was not found.");
		}

		$reflection = new \ReflectionClass($class);
		$class = $reflection->getName();

		if ($reflection->isAbstract()) {
			throw new InvalidPresenterException("Cannot load presenter '$name', class '$class' is abstract.");
		}

		$this->cache[$name] = $class;

		return $class;
	}


	/**
	 * Sets mapping as pairs [module => mask]
	 * @return static
	 */
	public function setMapping(array $mapping)
	{
		foreach ($mapping as $module => $mask) {
			if (is_string($mask)) {
				if (!preg_match('#^\\\\?([\w\\\\]*\\\\)?(\w*\*\w*?\\\\)?([\w\\\\]*\*\w*)\z#', $mask, $m)) {
					throw new Nette\InvalidStateException("Invalid mapping mask '$mask'.");
				}
				$this->mapping[$module] = [$m[1], $m[2] ?: '*Module\\', $m[3]];
			} elseif (is_array($mask) && count($mask) === 3) {
				$this->mapping[$module] = [$mask[0] ? $mask[0] . '\\' : '', $mask[1] . '\\', $mask[2]];
			} else {
				throw new Nette\InvalidStateException("Invalid mapping mask for module $module.");
			}
		}
		return $this;
	}


	/**
	 * Formats presenter class name from its name.
	 * @param  string
	 * @return string
	 * @internal
	 */
	public function formatPresenterClass($presenter)
	{
		$parts = explode(':', $presenter);
		$mapping = isset($parts[1], $this->mapping[$parts[0]])
			? $this->mapping[array_shift($parts)]
			: $this->mapping['*'];

		while ($part = array_shift($parts)) {
			$mapping[0] .= str_replace('*', $part, $mapping[$parts ? 1 : 2]);
		}
		return $mapping[0];
	}


	/**
	 * Formats presenter name from class name.
	 * @param  string
	 * @return string|NULL
	 * @internal
	 */
	public function unformatPresenterClass($class)
	{
		foreach ($this->mapping as $module => $mapping) {
			$mapping = str_replace(['\\', '*'], ['\\\\', '(\w+)'], $mapping);
			if (preg_match("#^\\\\?$mapping[0]((?:$mapping[1])*)$mapping[2]\\z#i", $class, $matches)) {
				return ($module === '*' ? '' : $module . ':')
					. preg_replace("#$mapping[1]#iA", '$1:', $matches[1]) . $matches[3];
			}
		}
		return NULL;
	}

}