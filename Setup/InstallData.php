<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace MagentoEse\VeniaSetup\Setup;

use Magento\Framework\Setup;


class InstallData implements Setup\InstallDataInterface
{
    /**
     * @var \Magento\Store\Model\StoreFactory
     */
    private $storeView;

    /**
     * @var \Magento\Store\Model\WebsiteFactory
     */
    private $websiteFactory;

    /**
     * @var \Magento\Store\Model\GroupFactory
     */
    private $groupFactory;

    /**
     * @var \Magento\Store\Model\ResourceModel\Group
     */
    private $groupResourceModel;

    /**
     * @var \Magento\Framework\App\State
     */
    private $state;

    public function __construct(\Magento\Store\Model\StoreFactory $storeView,
                                \Magento\Store\Model\WebsiteFactory $websiteFactory,
                                \Magento\Store\Model\GroupFactory $groupFactory,
                                \Magento\Store\Model\ResourceModel\Group $groupResourceModel,
                                \Magento\Catalog\Model\CategoryFactory $categoryFactory,
                                \Magento\Framework\App\State $state


    )
    {
        $this->storeView = $storeView;
        $this->websiteFactory = $websiteFactory;
        $this->groupFactory = $groupFactory;
        $this->groupResourceModel = $groupResourceModel;
        $this->categoryFactory = $categoryFactory;
        $this->config = require 'Config.php';
        try{
            $state->setAreaCode('adminhtml');
        }
        catch(\Magento\Framework\Exception\LocalizedException $e){
            // left empty
        }
    }




    public function install(Setup\ModuleDataSetupInterface $setup, Setup\ModuleContextInterface $moduleContext)
    {
        //create root catalog
        $rootCategoryId = $this->createCategory();

        //TODO:set default store view for venia store

        //get website
       $website = $this->websiteFactory->create();
        $website->load($this->config['website']);

        //create venia group
        if($website->getId()){

            $group = $this->groupFactory->create();
            $group->setWebsiteId($website->getWebsiteId());
            $group->setName($this->config['groupName']);
            $group->setRootCategoryId($rootCategoryId);
            //$group->setDefaultStoreId(3);
            $this->groupResourceModel->save($group);

            //create view
            $newStore = $this->storeView->create();
            $newStore->setName($this->config['newViewName']);
            $newStore->setCode($this->config['newViewCode']);
            $newStore->setWebsiteId($website->getId());
            $newStore->setGroupId($group->getId()); // GroupId is a Store ID (in adminhtml terms)
            $newStore->setSortOrder($this->config['newViewPriority']);
            $newStore->setIsActive(true);
            $newStore->save();
            //assign view as default on Venia store
            $group->setDefaultStoreId($newStore->getId());
            $group->save();
        }else{
            throw new \Magento\Framework\Exception\LocalizedException(__("default website does not exist, or venia already created"));

        }

    }
    protected function createCategory()
    {
          $data = [
                'parent_id' => 1,
                'name' => $this->config['rootCategoryName'],
                'is_active' => 1,
                'is_anchor' => 1,
                'include_in_menu' => 0,
                'position'=>10
            ];
            $category = $this->categoryFactory->create();
            $category->setData($data)
            ->setPath('1')
            ->setAttributeSetId($category->getDefaultAttributeSetId());
            $category->save();
            return $category->getId();

    }
}
