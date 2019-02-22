<?php

/*
 * @copyright   2019 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\CustomObjectsBundle\Tests\EventListener;

use MauticPlugin\CustomObjectsBundle\Provider\ConfigProvider;
use MauticPlugin\CustomObjectsBundle\Model\CustomObjectModel;
use MauticPlugin\CustomObjectsBundle\Model\CustomItemImportModel;
use MauticPlugin\CustomObjectsBundle\EventListener\ImportSubscriber;
use Mautic\LeadBundle\Event\ImportInitEvent;
use Mautic\LeadBundle\Event\ImportMappingEvent;
use Mautic\LeadBundle\Event\ImportProcessEvent;
use MauticPlugin\CustomObjectsBundle\Entity\CustomObject;
use MauticPlugin\CustomObjectsBundle\Provider\CustomItemRouteProvider;

class ImportSubscriberTest extends \PHPUnit_Framework_TestCase
{
    private $customObjectModel;
    private $customItemImportModel;
    private $configProvider;
    private $importInitEvent;
    private $importMappingEvent;
    private $importProcessEvent;
    private $importSubscriber;

    protected function setUp()
    {
        parent::setUp();

        $this->customObjectModel     = $this->createMock(CustomObjectModel::class);
        $this->customItemImportModel = $this->createMock(CustomItemImportModel::class);
        $this->configProvider        = $this->createMock(ConfigProvider::class);
        $this->importInitEvent       = $this->createMock(ImportInitEvent::class);
        $this->importMappingEvent    = $this->createMock(ImportMappingEvent::class);
        $this->importProcessEvent    = $this->createMock(ImportProcessEvent::class);
        $this->importSubscriber      = new ImportSubscriber(
            $this->customObjectModel,
            $this->customItemImportModel,
            $this->configProvider
        );
    }

    public function testPluginDisabledForImportInit()
    {
        $this->configProvider->expects($this->once())
            ->method('pluginIsEnabled')
            ->willReturn(false);

        $this->importInitEvent->expects($this->never())
            ->method('getRouteObjectName');
        
        $this->importSubscriber->onImportInit($this->importInitEvent);
    }

    public function testImportInit()
    {
        $customObject = $this->createMock(CustomObject::class);

        $customObject->method('getNamePlural')->willReturn('Test Object');

        $this->configProvider->expects($this->once())
            ->method('pluginIsEnabled')
            ->willReturn(true);

        $this->importInitEvent->expects($this->exactly(2))
            ->method('getRouteObjectName')
            ->willReturn('custom-object:35');

        $this->customObjectModel->expects($this->once())
            ->method('fetchEntity')
            ->with(35)
            ->willReturn($customObject);

        $this->importInitEvent->expects($this->once())
            ->method('setObjectIsSupported')
            ->with(true);

        $this->importInitEvent->expects($this->once())
            ->method('setObjectSingular')
            ->with('custom-object:35');

        $this->importInitEvent->expects($this->once())
            ->method('setObjectName')
            ->with('Test Object');

        $this->importInitEvent->expects($this->once())
            ->method('setActiveLink')
            ->with('#mautic_custom_object_35');

        $this->importInitEvent->expects($this->once())
            ->method('setIndexRoute')
            ->with(CustomItemRouteProvider::ROUTE_LIST, ['objectId' => 35]);

        $this->importInitEvent->expects($this->once())
            ->method('stopPropagation');
        
        $this->importSubscriber->onImportInit($this->importInitEvent);
    }

    public function testPluginDisabledForFieldMapping()
    {
        $this->configProvider->expects($this->once())
            ->method('pluginIsEnabled')
            ->willReturn(false);

        $this->importInitEvent->expects($this->never())
            ->method('getRouteObjectName');
        
        $this->importSubscriber->onFieldMapping($this->importMappingEvent);
    }

    public function testPluginDisabledForImportProcess()
    {
        $this->configProvider->expects($this->once())
            ->method('pluginIsEnabled')
            ->willReturn(false);

        $this->importProcessEvent->expects($this->never())
            ->method('getImport');
        
        $this->importSubscriber->onImportProcess($this->importProcessEvent);
    }
}