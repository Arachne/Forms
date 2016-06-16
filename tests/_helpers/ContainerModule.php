<?php

namespace Tests;

use Arachne\Bootstrap\Configurator;
use Codeception\Module;
use Nette\Utils\FileSystem;

class ContainerModule extends Module
{
    /**
     * @var string
     */
    private $tempDir;

    public function _beforeSuite($settings = [])
    {
        $this->tempDir = $settings['path'].'/../_temp/'.pathinfo($settings['path'], PATHINFO_BASENAME);
        FileSystem::delete($this->tempDir);
        FileSystem::createDir($this->tempDir);
    }

    public function _afterSuite()
    {
        FileSystem::delete($this->tempDir);
    }

    public function createContainer($configFile)
    {
        $configurator = new Configurator();
        $configurator->setTempDirectory($this->tempDir);
        $configurator->addConfig($configFile, false);
        return $configurator->createContainer();
    }
}
