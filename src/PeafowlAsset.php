<?php
namespace kilyakus\peafowl;

class Peafowl extends \kilyakus\widgets\AssetBundle
{
    public function init()
    {
        $this->setSourcePath(__DIR__ . '/assets');
        $this->setupAssets('css', ['css/peafowl'],'widget-peafowl');
        parent::init();
    }
}
