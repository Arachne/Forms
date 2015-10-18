<?php

namespace Tests\Integration;

use Arachne\Bootstrap\Configurator;
use Codeception\TestCase\Test;

/**
 * @author Jáchym Toušek <enumag@gmail.com>
 */
class FormsExtensionTest extends Test
{

	public function testServices()
	{
		$container = $this->createContainer('config.neon');

		$this->assertInstanceOf('Nette\DI\Container', $container);
	}

	private function createContainer($file)
	{
		$config = new Configurator();
		$config->setTempDirectory(TEMP_DIR);
		$config->addParameters([ 'container' => [ 'class' => 'SystemContainer_' . md5(time()) ] ]);
		$config->addConfig(__DIR__ . '/../config/' . $file, false);
		return $config->createContainer();
	}

}
