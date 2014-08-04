<?php

namespace OroCRM\Bundle\ChannelBundle\Tests\Unit\Provider;

use Oro\Bundle\EntityBundle\Provider\EntityProvider;
use Oro\Bundle\EntityConfigBundle\Config\ConfigManager;

use Oro\Bundle\EntityConfigBundle\Entity\EntityConfigModel;
use OroCRM\Bundle\ChannelBundle\Provider\MetadataProvider;
use OroCRM\Bundle\ChannelBundle\Provider\SettingsProvider;

class MetadataProviderTest extends \PHPUnit_Framework_TestCase
{
    const ENTITY_ID1 = 99;
    const ENTITY_ID2 = 68;


    /** @var array */
    protected $expected = [
        'OroCRM\Bundle\TestBundle1\Entity\Entity1' => [
            'name'                   => 'OroCRM\Bundle\TestBundle1\Entity\Entity1',
            'dependent'              => [],
            'navigation_items'       => [],
            'dependencies'           => [],
            'dependencies_condition' => 'OR',
            'belongs_to'             => ['integration' => 'testIntegrationType']
        ],
        'OroCRM\Bundle\TestBundle1\Entity\Entity2' => [
            'name'                   => 'OroCRM\Bundle\TestBundle2\Entity\Entity2',
            'dependent'              => [],
            'navigation_items'       => [],
            'dependencies'           => [],
            'dependencies_condition' => 'AND',
            'belongs_to'             => ['integration' => 'testIntegrationType']
        ],
        'OroCRM\Bundle\TestBundle2\Entity\Entity3' => [
            'name'                   => 'OroCRM\Bundle\TestBundle2\Entity\Entity3',
            'dependent'              => [],
            'navigation_items'       => [],
            'dependencies'           => [],
            'dependencies_condition' => 'AND',
        ],
    ];

    /** @var array */
    protected $entity1Config = [
        'name'         => 'OroCRM\Bundle\TestBundle1\Entity\Entity1',
        'label'        => 'Entity 1',
        'plural_label' => 'Entities 1',
        'icon'         => '',
        'entity_id'    => self::ENTITY_ID1,
    ];

    /** @var array */
    protected $entity2Config = [
        'name'         => 'OroCRM\Bundle\TestBundle1\Entity\Entity2',
        'label'        => 'Entity 2',
        'plural_label' => 'Entities 2',
        'icon'         => '',
        'entity_id'    => self::ENTITY_ID2,
    ];

    /** @var array */
    protected $entity3Config = [
        'name'         => 'OroCRM\Bundle\TestBundle1\Entity\Entity3',
        'label'        => 'Entity 3',
        'plural_label' => 'Entities 3',
        'icon'         => ''
    ];

    /** @var SettingsProvider|\PHPUnit_Framework_MockObject_MockObject */
    protected $settingsProvider;

    /** @var  EntityProvider|\PHPUnit_Framework_MockObject_MockObject */
    protected $entityProvider;

    /** @var ConfigManager|\PHPUnit_Framework_MockObject_MockObject */
    protected $configManager;

    /** @var EntityConfigModel|\PHPUnit_Framework_MockObject_MockObject */
    protected $entityConfigModel;

    public function setUp()
    {
        $this->settingsProvider = $this->getMockBuilder('OroCRM\Bundle\ChannelBundle\Provider\SettingsProvider')
            ->disableOriginalConstructor()->getMock();
        $this->entityProvider   = $this->getMockBuilder('Oro\Bundle\EntityBundle\Provider\EntityProvider')
            ->disableOriginalConstructor()->getMock();
        $this->configManager    = $this->getMockBuilder('Oro\Bundle\EntityConfigBundle\Config\ConfigManager')
            ->disableOriginalConstructor()->getMock();
        $this->entityConfigModel = $this->getMockBuilder('Oro\Bundle\EntityConfigBundle\Entity\EntityConfigModel')
            ->disableOriginalConstructor()->getMock();
    }

    public function testGetListOfIntegrationEntities()
    {
        $this->settingsProvider->expects($this->once())
            ->method('getSettings')
            ->will($this->returnvalue($this->expected));

        $this->entityProvider->expects($this->at(0))
            ->method('getEntity')
            ->will($this->returnvalue($this->entity1Config));
        $this->entityProvider->expects($this->at(1))
            ->method('getEntity')
            ->will($this->returnvalue($this->entity2Config));

        $this->configManager->expects($this->exactly(2))
            ->method('getConfigEntityModel')
            ->will($this->returnvalue($this->entityConfigModel));

        $this->entityConfigModel->expects($this->at(0))
            ->method('getId')
            ->will($this->returnvalue(self::ENTITY_ID1));
        $this->entityConfigModel->expects($this->at(1))
            ->method('getId')
            ->will($this->returnvalue(self::ENTITY_ID2));

        /** @var MetadataProvider $provider */
        $provider = new MetadataProvider($this->settingsProvider, $this->entityProvider, $this->configManager);
        $result   = $provider->getMetadataList();

        $this->assertArrayHasKey('testIntegrationType', $result);
        $this->assertCount(2, $result['testIntegrationType']);

        for ($i=0; $i<2; $i++) {
            $configName = 'entity' . ($i+1) . 'Config';
            $this->assertEquals($result['testIntegrationType'][$i], $this->$configName);
        }
    }
}
