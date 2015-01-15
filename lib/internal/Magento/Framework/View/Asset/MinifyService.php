<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Asset;

/**
 * Service model responsible for configuration of minified asset
 */
class MinifyService
{
    /**
     * Config
     *
     * @var ConfigInterface
     */
    protected $config;

    /**
     * ObjectManager
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * Enabled
     *
     * @var array
     */
    protected $enabled = [];

    /**
     * @var \Magento\Framework\Code\Minifier\AdapterInterface[]
     */
    protected $adapters = [];

    /**
     * @var string
     */
    protected $appMode;

    /**
     * Constructor
     *
     * @param ConfigInterface $config
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param string $appMode
     */
    public function __construct(
        ConfigInterface $config,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        $appMode = \Magento\Framework\App\State::MODE_DEFAULT
    ) {
        $this->config = $config;
        $this->objectManager = $objectManager;
        $this->appMode = $appMode;
    }

    /**
     * Get filtered assets
     * Assets applicable for minification are wrapped with the minified asset
     *
     * @param array|\Iterator $assets
     * @return \Magento\Framework\View\Asset\Minified\AbstractAsset[]
     */
    public function getAssets($assets)
    {
        $resultAssets = [];
        $strategy = $this->appMode == \Magento\Framework\App\State::MODE_PRODUCTION
            ? Minified\AbstractAsset::FILE_EXISTS : Minified\AbstractAsset::MTIME;
        /** @var $asset AssetInterface */
        foreach ($assets as $asset) {
            if ($this->isEnabled($asset->getContentType())) {
                $asset = $this->getAssetDecorated($asset, $strategy);
            }
            $resultAssets[] = $asset;
        }
        return $resultAssets;
    }

    /**
     * Check if minification is enabled for specified content type
     *
     * @param string $contentType
     * @return bool
     */
    protected function isEnabled($contentType)
    {
        if (!isset($this->enabled[$contentType])) {
            $this->enabled[$contentType] = $this->config->isAssetMinification($contentType);
        }
        return $this->enabled[$contentType];
    }

    /**
     * Get minification adapter by specified content type
     *
     * @param string $contentType
     * @return \Magento\Framework\Code\Minifier\AdapterInterface
     * @throws \Magento\Framework\Exception
     */
    protected function getAdapter($contentType)
    {
        if (!isset($this->adapters[$contentType])) {
            $adapterClass = $this->config->getAssetMinificationAdapter($contentType);
            if (!$adapterClass) {
                throw new \Magento\Framework\Exception(
                    "Minification adapter is not specified for '$contentType' content type"
                );
            }
            $adapter = $this->objectManager->get($adapterClass);
            if (!($adapter instanceof \Magento\Framework\Code\Minifier\AdapterInterface)) {
                $type = get_class($adapter);
                throw new \Magento\Framework\Exception(
                    "Invalid adapter: '{$type}'. Expected: \\Magento\\Framework\\Code\\Minifier\\AdapterInterface"
                );
            }
            $this->adapters[$contentType] = $adapter;
        }
        return $this->adapters[$contentType];
    }

    /**
     * @param AssetInterface $asset
     * @param string $strategy
     * @return AssetInterface
     * @throws \Magento\Framework\Exception
     */
    protected function getAssetDecorated(AssetInterface $asset, $strategy)
    {
        return $this->objectManager->create(
                    $this->getDecoratorClass($asset),
                    [
                        'asset' => $asset,
                        'strategy' => $strategy,
                        'adapter' => $this->getAdapter($asset->getContentType()),
                    ]
        );
    }

    /**
     * @param AssetInterface $asset
     * @return string
     */
    protected function getDecoratorClass(AssetInterface $asset)
    {
        if ($asset->getContentType() == 'css') {
            $result = 'Magento\Framework\View\Asset\Minified\ImmutablePathAsset';
        } else {
            $result = 'Magento\Framework\View\Asset\Minified\MutablePathAsset';
        }
        return $result;
    }
}
