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
                                \Magento\Framework\App\State $state


    )
    {
        $this->storeView = $storeView;
        $this->websiteFactory = $websiteFactory;
        $this->groupFactory = $groupFactory;
        $this->groupResourceModel = $groupResourceModel;
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

        //TODO:set default store view for venia store

        //get website
        $website = $this->websiteFactory->create();
        $website->load($this->config['website']);

        //create venia group
        if($website->getId()){
            /** @var \Magento\Store\Model\Group $group */
            $group = $this->groupFactory->create();
            $group->setWebsiteId($website->getWebsiteId());
            $group->setName($this->config['groupName']);
           // $group->setRootCategoryId(2);
            //$group->setDefaultStoreId(3);
            $this->groupResourceModel->save($group);
            $newStore = $this->storeView->create();
            $newStore->setName($this->config['newViewName']);
            $newStore->setCode($this->config['newViewCode']);
            $newStore->setWebsiteId($website->getId());
            $newStore->setGroupId($group->getId()); // GroupId is a Store ID (in adminhtml terms)
            $newStore->setSortOrder($this->config['newViewPriority']);
            $newStore->setIsActive(true);
            $newStore->save();
        }else{
            throw new \Magento\Framework\Exception\LocalizedException(__("default website does not exist, or venia already created"));

        }

        //$websiteId = $this->websiteRepository->get($this->config['website'])->getId();

        //get groups (stores in website)
        //$_websiteGroups = $this->website->load($this->config['website'])->getGroups();

        //get id of group
        /*foreach ($_websiteGroups as $group){
            if($group->getName()==$this->config['groupName']){
                $_groupId = $group->getId();
                break;
            }
        }*/
        //Change name of default store
        /*$defaultStore = $this->storeView->create();
        $defaultStore->load('default');
        $defaultStore->setName($this->config['defaultStoreName']);
        $defaultStore->save();*/

        //add new store
        /*$newStore = $this->storeView->create();
        $newStore->setName($this->config['newViewName']);
        $newStore->setCode($this->config['newViewCode']);
        $newStore->setWebsiteId($websiteId);
        $newStore->setGroupId($_groupId); // GroupId is a Store ID (in adminhtml terms)
        $newStore->setSortOrder($this->config['newViewPriority']);
        $newStore->setIsActive(true);
        $newStore->save();*/
    }
}
