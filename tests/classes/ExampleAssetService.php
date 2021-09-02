<?php

namespace WonderWp\Component\Asset\Tests\classes;

use WonderWp\Component\Asset\AbstractAssetService;
use WonderWp\Component\Asset\Asset;

class ExampleAssetService extends AbstractAssetService
{

    public function getAssets()
    {
        $this->assets = [
            'css' => [
                new Asset('first-css', '/fixtures/css/first.css', ['styleguide'], null, false, 'first-group'),
                new Asset('second-css', '/fixtures/css/second.css', ['admin'], null, false, 'admin'),
                new Asset('third-css', '/fixtures/css/third.css', ['admin'], null, false, 'first-group')
            ],
            'js'  => [
                new Asset('first-js', '/fixtures/js/first.js', ['second-js'], null, false, 'first-group'),
                new Asset('second-js', '/fixtures/js/second.js', [], null, false, 'first-group'),
                new Asset('third-js', '/fixtures/js/third.js', [], null, false, 'second-group'),
            ],
        ];

        return $this->assets;
    }
}
