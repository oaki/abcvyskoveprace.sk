<?php

namespace Oaki\Cart\DI;

use Nette\DI\CompilerExtension;
use Nette\DI\Statement;

class Extension extends CompilerExtension
{

    private function getDefaultConfig()
    {
        return [
            'items' => new Statement('@session::getSection', [__CLASS__])
        ];
    }

    public function loadConfiguration()
    {
        $config  = $this->getConfig($this->getDefaultConfig());
        $builder = $this->getContainerBuilder();

        $builder->addDefinition($this->prefix('cart'))
            ->setClass('Oaki\Cart\Cart', [$config['items']]);

        $builder->addDefinition($this->prefix('cartControlFactory'))
            ->setImplement('Oaki\Cart\ICartControlFactory');
    }

}
