<?php
/**
 * Vite plugin for Craft CMS 3.x
 *
 * Allows the use of the Vite.js next generation frontend tooling with Craft CMS
 *
 * @link      https://nystudio107.com
 * @copyright Copyright (c) 2021 nystudio107
 */

namespace nystudio107\pluginvite\services;

use nystudio107\pluginvite\helpers\FileHelper;

use Craft;

/**
 * @author    nystudio107
 * @package   Vite
 * @since     1.0.0
 */
class VitePluginService extends ViteService
{
    // Constants
    // =========================================================================

    const MANIFEST_FILE_NAME = 'manifest.json';

    // Public Properties
    // =========================================================================

    /**
     * @var string AssetBundle class name to get the published URLs from
     */
    public $assetClass;

    /**
     * @var string The environment variable to look for in order to enable the devServer; the value doesn't matter,
     *              it just needs to exist
     */
    public $pluginDevServerEnvVar = 'VITE_PLUGIN_DEVSERVER';

    /**
     * @inheritDoc
     */
    public function init()
    {
        parent::init();
        // Only bother if this is a CP request
        $request = Craft::$app->getRequest();
        if (!$request->getIsCpRequest()) {
            return;
        }
        $this->invalidateCaches();
        // See if the $pluginDevServerEnvVar env var exists, and if not, don't run off of the dev server
        $useDevServer = getenv($this->pluginDevServerEnvVar);
        if ($useDevServer === false) {
            $this->useDevServer = false;
        }
        // If we have no asset bundle class, or the dev server is running, don't swap in our `/cpresources/` paths
        if (!$this->assetClass || $this->devServerRunning()) {
            return;
        }
        // If we're in a plugin, make sure the caches are unique
        if ($this->assetClass) {
            $this->cacheKeySuffix = $this->assetClass;
        }
        // Map the $manifestPath and $serverPublic to the hashed `/cpresources/` path & URL for our AssetBundle
        $bundle = new $this->assetClass();
        $baseAssetsUrl = Craft::$app->assetManager->getPublishedUrl(
            $bundle->sourcePath,
            true
        );
        $this->manifestPath = FileHelper::createUrl($bundle->sourcePath, self::MANIFEST_FILE_NAME);
        if ($baseAssetsUrl !== false) {
            $this->serverPublic = $baseAssetsUrl;
        }
    }
}
