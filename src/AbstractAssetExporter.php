<?php

namespace WonderWp\Component\Asset;

use WonderWp\Component\DependencyInjection\Container;
use WonderWp\Component\Logging\AbstractLogger;
use WonderWp\Component\Logging\DirectOutputLogger;

abstract class AbstractAssetExporter implements AssetExporterInterface
{
    /** @var Container */
    protected $container;

    /** @var DirectOutputLogger */
    protected $logger;


    /** @inheritdoc */
    public function __invoke($args)
    {
        $this->container = Container::getInstance();
        $this->export();
    }

    /**
     * @param $res
     */
    public function respond($res)
    {
        $this->logger = new DirectOutputLogger();
        if (is_array($res) && $res['code'] && $res['code'] == 200) {
            $this->logger->log(AbstractLogger::SUCCESS, $res['data']['msg']);
        } else {
            $errorMsg = (is_array($res) && $res['data'] && $res['data']['msg']) ? $res['data']['msg'] : 'error';
            $this->logger->log(AbstractLogger::ERROR, $errorMsg);
        }
    }
}
