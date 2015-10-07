<?php

/**
 * This file is part of the Arachne
 *
 * Copyright (c) Jáchym Toušek (enumag@gmail.com)
 *
 * For the full copyright and license information, please view the file license.md that was distributed with this source code.
 */

namespace Arachne\Twig\DI;

use Nette\DI\CompilerExtension;
use Nette\DI\Statement;

/**
 * @author Jáchym Toušek <enumag@gmail.com>
 */
class TwigExtension extends CompilerExtension
{

	const TAG_EXTENSION = 'arachne.twig.extension';
	const TAG_LOADER = 'arachne.twig.loader';

	public $defaults = [
		'options' => [
			'strict_variables' => true,
			'cache' => null,
		],
		'paths' => [],
	];
	
	/** @var array */
	private $paths = [];

	public function __construct($debugMode = false)
	{
		$this->defaults['options']['debug'] = $debugMode;
	}

	/**
	 * @param string $path
	 * @param string $namespace	 
	 */
	public function addPath($path, $namespace = null)
	{
		$loader = $this->getContainerBuilder()->getDefinition($this->prefix('loader.fileSystem'));
		if ($namespace) {
			$loader->addSetup('?->addPath(?, ?)', [ '@self', $path, $namespace ]);
		} else {
			$loader->addSetup('?->addPath(?)', [ '@self', $path ]);
		}
	}

	public function loadConfiguration()
	{
		$this->validateConfig($this->defaults);
		$builder = $this->getContainerBuilder();

		$builder->addDefinition($this->prefix('environment'))
			->setClass('Twig_Environment')
			->setArguments([
				'options' => $this->config['options'],
			]);

		$builder->addDefinition($this->prefix('loader'))
			->setClass('Twig_Loader_Chain');

		$builder->addDefinition($this->prefix('loader.fileSystem'))
			->setClass('Twig_Loader_Filesystem')
			->addTag(self::TAG_LOADER)
			->setAutowired(false);

		$builder->addDefinition($this->prefix('extension.dump'))
			->setClass('Arachne\Twig\Extension\DumpExtension')
			->addTag(self::TAG_EXTENSION)
			->setAutowired(false);
	}

	public function beforeCompile()
	{
		$builder = $this->getContainerBuilder();

		$builder->getDefinition($this->prefix('loader'))
			->setArguments([
				'loaders' => array_map(function ($service) {
					return '@' . $service;
				}, array_keys($builder->findByTag(self::TAG_LOADER))),
			]);

		$environment = $builder->getDefinition($this->prefix('environment'));
		foreach ($builder->findByTag(self::TAG_EXTENSION) as $service => $attributes) {
			$environment->addSetup('?->addExtension(?)', [ '@self', '@' . $service ]);
		}

		foreach ($this->config['paths'] as $path => $namespace) {
			if (is_string($path)) {
				$this->addPath($path, $namespace);
			} else {
				$this->addPath($namespace);
			}
		}
	}

}
