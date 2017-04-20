<?php
/**
 * Configuration for Venia Store
 *
 * @category Sample_Data
 * @package  MagentoEse_VeniaSetup
 * @author   Jeff Britts <jbritts@magento.com>
 * @license  See COPYING.txt for license details.
 * @link     http://magento.com
 * Copyright © 2017 Magento. All rights reserved.
 */
namespace MagentoEse\VeniaSetup\Setup;

use Magento\Framework\Setup;

/**
 * InstallData Class
 *
 * @category Sample_Data
 * @package  MagentoEse_VeniaSetup
 * @author   Jeff Britts <jbritts@magento.com>
 * @license  See COPYING.txt for license details.
 * @link     http://magento.com
 * Copyright © 2017 Magento. All rights reserved.
 */
class InstallData implements Setup\InstallDataInterface
{
    /**
     * Store View
     *
     * @var \Magento\Store\Api\Data\StoreInterfaceFactory
     */
    private $_storeView;

    /**
     * Website Factory
     *
     * @var \Magento\Store\Api\Data\WebsiteInterfaceFactory
     */
    private $_websiteFactory;

    /**
     * Group Factory
     *
     * @var \Magento\Store\Api\Data\GroupInterfaceFactory
     */
    private $_groupFactory;

    /**
     * Group Resource
     *
     * @var \Magento\Store\Model\ResourceModel\Group
     */
    private $_groupResourceModel;

    /**
     * Category Factory
     *
     * @var \Magento\Catalog\Api\Data\CategoryInterfaceFactory
     */
    private $_categoryFactory;

    /**
     * Area Code
     *
     * @var \Magento\Framework\App\State
     */
    private $_state;

    /**
     * Resource Configuration
     *
     * @var \Magento\Config\Model\ResourceModel\Config
     */
    private $resourceConfig;

    /**
     * Theme Collection
     *
     * @var \Magento\Theme\Model\ResourceModel\Theme\Collection
     */
    private $themeCollection;

    /**
     * Theme registration
     *
     * @var \Magento\Theme\Model\Theme\Registration
     */
    private $themeRegistration;

    /**
     * Sales setup factory
     *
     * @var \Magento\SalesSequence\Model\EntityPool
     */
    private $entityPool;

    /**
     * @var \Magento\SalesSequence\Model\Builder
     */
    private $sequenceBuilder;

    /**
     * @var \Magento\SalesSequence\Model\Config
     */
    private $sequenceConfig;

    /**
     * Constructor
     *
     * @param \Magento\Store\Api\Data\StoreInterfaceFactory      $_storeView          Store View
     * @param \Magento\Store\Api\Data\WebsiteInterfaceFactory    $_websiteFactory     Website Factory
     * @param \Magento\Store\Api\Data\GroupInterfaceFactory      $_groupFactory       Group Factory
     * @param \Magento\Store\Model\ResourceModel\Group           $_groupResourceModel Group ResourceModel
     * @param \Magento\Catalog\Api\Data\CategoryInterfaceFactory $_categoryFactory    Category Factory
     * @param \Magento\Framework\App\State                       $_state              Area Code
     * @param \Magento\Config\Model\ResourceModel\Config         $resourceConfig      Resoource config
     * @param \Magento\Theme\Model\ResourceModel\Theme\Collection $themeCollection    Theme Collection
     * @param \Magento\Theme\Model\Theme\Registration            $themeRegistration   Theme Registration
     * @param \Magento\SalesSequence\Model\EntityPool             $entityPool           Entity Pool
     * @param \Magento\SalesSequence\Model\Builder              $sequenceBuilder       Sequence Builder
     * @param  \Magento\SalesSequence\Model\Config              $sequenceConfig         Sequence Config
     */
    public function __construct(
        \Magento\Store\Api\Data\StoreInterfaceFactory $_storeView,
        \Magento\Store\Api\Data\WebsiteInterfaceFactory $_websiteFactory,
        \Magento\Store\Api\Data\GroupInterfaceFactory $_groupFactory,
        \Magento\Store\Model\ResourceModel\Group $_groupResourceModel,
        \Magento\Catalog\Api\Data\CategoryInterfaceFactory $_categoryFactory,
        \Magento\Framework\App\State $_state,
        \Magento\Config\Model\ResourceModel\Config $resourceConfig,
        \Magento\Theme\Model\ResourceModel\Theme\Collection $themeCollection,
        \Magento\Theme\Model\Theme\Registration $themeRegistration,
        \Magento\SalesSequence\Model\EntityPool $entityPool,
        \Magento\SalesSequence\Model\Builder $sequenceBuilder,
        \Magento\SalesSequence\Model\Config $sequenceConfig
    ) {
    
        $this->storeView = $_storeView;
        $this->websiteFactory = $_websiteFactory;
        $this->groupFactory = $_groupFactory;
        $this->groupResourceModel = $_groupResourceModel;
        $this->categoryFactory = $_categoryFactory;
        $this->config = include 'Config.php';
        $this->_resourceConfig = $resourceConfig;
        $this->themeCollection = $themeCollection;
        $this->themeRegistration = $themeRegistration;
        $this->entityPool = $entityPool;
        $this->sequenceBuilder = $sequenceBuilder;
        $this->sequenceConfig = $sequenceConfig;
        try{
            $_state->setAreaCode('adminhtml');
        }
        catch(\Magento\Framework\Exception\LocalizedException $e){
            // left empty
        }
    }


    /**
     * Install - Create Root Catalog, Group, View
     *
     * @param Setup\ModuleDataSetupInterface $setup         Setup
     * @param Setup\ModuleContextInterface   $moduleContext Module Context
     * 
     * @throws \Magento\Framework\Exception\LocalizedException
     *
     * @return null
     */
    public function install(Setup\ModuleDataSetupInterface $setup,
        Setup\ModuleContextInterface $moduleContext
    ) {
    
        //create root catalog
        //$rootCategoryId = $this->createCategory();


        //get website
        $website = $this->websiteFactory->create();
        $website->load($this->config['website']);

        //create venia group/store
        if ($website->getId()) {
            $group = $this->groupFactory->create();
            $group->setWebsiteId($website->getWebsiteId());
            $group->setName($this->config['groupName']);
            //$group->setRootCategoryId($rootCategoryId);
            $this->groupResourceModel->save($group);


            //create view
            $newStore = $this->storeView->create();
            $newStore->setName($this->config['newViewName']);
            $newStore->setCode($this->config['newViewCode']);
            $newStore->setWebsiteId($website->getId());
            // GroupId is a Store ID (in adminhtml terms)
            $newStore->setGroupId($group->getId());
            $newStore->setSortOrder($this->config['newViewPriority']);
            $newStore->setIsActive(true);
            $newStore->save();
            //assign view as default on Venia store
            $group->setDefaultStoreId($newStore->getId());
            $group->save();
            //add sequences
            foreach ($this->entityPool->getEntities() as $entityType) {
                $this->sequenceBuilder->setPrefix($this->sequenceConfig->get('prefix'))
                    ->setSuffix($this->sequenceConfig->get('suffix'))
                    ->setStartValue($this->sequenceConfig->get('startValue'))
                    ->setStoreId($newStore->getId())
                    ->setStep($this->sequenceConfig->get('step'))
                    ->setWarningValue($this->sequenceConfig->get('warningValue'))
                    ->setMaxValue($this->sequenceConfig->get('maxValue'))
                    ->setEntityType($entityType)->create();
            }

            //make sure theme is registered
            $this->themeRegistration->register();
            $themeId = $this->themeCollection->getThemeByFullPath('frontend/MagentoEse/venia')->getThemeId();
            //set theme for Venia store
            $this->_resourceConfig->saveConfig("design/theme/theme_id", $themeId, "stores", $newStore->getId());
            //set venia description used by store switcher
            $this->_resourceConfig->saveConfig("general/store_information/description", $this->config['veniaDescription'], "stores", $newStore->getId());
            //set luma description used by store switcher
            $lumaStore = $this->storeView->create();
            $lumaStoreId=$lumaStore->load('default')->getId();
            $this->_resourceConfig->saveConfig("general/store_information/description", $this->config['lumaDescription'], "stores", $lumaStoreId);
        } else {
            throw new \Magento\Framework\Exception\LocalizedException(__("default website does not exist, or venia already created"));

        }

    }

    /**
     * Creates Category and returns new category id
     *
     * @return int|null
     */
    protected function createCategory()
    {
          $data = [
                'parent_id' => 1,
                'name' => $this->config['rootCategoryName'],
                'is_active' => 1,
                'is_anchor' => 1,
                'include_in_menu' => 0,
                'position'=>10,
                'store_id'=>0
            ];
            $category = $this->categoryFactory->create();
            $foo=$category->getDefaultAttributeSetId();
            $category->setData($data)
                ->setPath('1')
                ->setAttributeSetId($category->getDefaultAttributeSetId());
            $category->save();
            return $category->getId();

    }
}
