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
                new Asset('first-css', '/fixtures/css/first.css', ['second-css'], null, false, 'admin'),
                new Asset('second-css', '/fixtures/css/second.css', [], null, false, 'styleguide'),
                new Asset('third-css', '/fixtures/css/third.css', [], null, false, 'styleguide')
            ],
            'js' => [
                new Asset('first-js', '/fixtures/js/first.js', ['second-js'], null, false, 'admin'),
                new Asset('second-js', '/fixtures/js/second.js', [], null, false, 'styleguide'),
                new Asset('third-js', '/fixtures/js/third.js', [], null, false, 'styleguide'),
            ],
        ];

        return $this->assets;
    }
}
