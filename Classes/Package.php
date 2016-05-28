<?php
namespace Neos\MetaData\Extractor;

/*
 * This file is part of the Neos.MetaData.Extractor package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neos\MetaData\Extractor\Domain\ExtractionManager;
use TYPO3\Flow\Configuration\ConfigurationManager;
use TYPO3\Flow\Core\Booting\Sequence;
use TYPO3\Flow\Core\Bootstrap;
use TYPO3\Flow\Package\Package as BasePackage;
use TYPO3\Media\Domain\Model\Asset;
use TYPO3\Flow\Core\Booting\Step;

class Package extends BasePackage {


    /**
     * Invokes custom PHP code directly after the package manager has been initialized.
     *
     * @param Bootstrap $bootstrap The current bootstrap
     *
     * @return void
     */
    public function boot(Bootstrap $bootstrap) {
        $dispatcher = $bootstrap->getSignalSlotDispatcher();
        $package = $this;
        $dispatcher->connect(Sequence::class, 'afterInvokeStep', function(Step $step) use ($package, $bootstrap) {
            if ($step->getIdentifier() === 'typo3.flow:reflectionservice') {
                $package->registerExtractionSlot($bootstrap);
            }
        });
    }

    /**
     * Registers slots for signals in order to be able to index nodes
     *
     * @param Bootstrap $bootstrap
     */
    public function registerExtractionSlot(Bootstrap $bootstrap) {
        $configurationManager = $bootstrap->getObjectManager()->get(ConfigurationManager::class);
        $settings = $configurationManager->getConfiguration(ConfigurationManager::CONFIGURATION_TYPE_SETTINGS, $this->getPackageKey());

        if (isset($settings['realtimeExtraction']['enabled']) && $settings['realtimeExtraction']['enabled'] === TRUE) {
            $dispatcher = $bootstrap->getSignalSlotDispatcher();
            $dispatcher->connect(Asset::class, 'assetCreated', ExtractionManager::class, 'extractMetaData');
        }
    }
}