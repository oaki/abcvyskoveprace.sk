<?php

class MemcachedSession extends SessionHandler
{

    public function read($session_id)
    {
        return (string)parent::read($session_id);
    }
}

$memcacheSessionSaveHandler = new MemcachedSession();
session_set_save_handler($memcacheSessionSaveHandler, true);

require __DIR__ . '/../vendor/autoload.php';

$configurator = new Nette\Configurator;

$configurator->addParameters(['syncLogDir' => __DIR__ . '/../log/syncLog']);

$configurator->setDebugMode(['78.99.152.195']); // enable for your remote IP
$configurator->enableDebugger(__DIR__ . '/../log', 'pavol@bincik.sk');
error_reporting(~E_USER_DEPRECATED);
$configurator->setTempDirectory(__DIR__ . '/../temp');

$configurator->createRobotLoader()
    ->addDirectory(__DIR__)
    ->register();

$configurator->addConfig(__DIR__ . '/config/config.neon');

if (isset($_SERVER['APPLICATION_ENV']) and $_SERVER['APPLICATION_ENV'] == 'dev') {
    $configurator->addConfig(__DIR__ . '/config/config.dev.db.neon');
} else {
    $configurator->addConfig(__DIR__ . '/config/config.db.neon');
}

$configurator->addConfig(__DIR__ . '/config/config.services.neon');

$container = $configurator->createContainer();
\Oaki\NettePanel\CallbackPanel::register($container);

function dd($var)
{
    foreach (func_get_args() as $arg) {
        dump($arg);
    }

    return $var;
}

function dde()
{
    foreach (func_get_args() as $arg) {
        dump($arg);
    }
    exit;

}

return $container;
