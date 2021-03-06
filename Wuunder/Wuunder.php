<?php

namespace Wuunder;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\SchemaTool;
use Enlight_Event_EventArgs;
use Shopware\Components\Plugin;
use Shopware\Components\Plugin\Context\ActivateContext;
use Shopware\Components\Plugin\Context\DeactivateContext;
use Shopware\Components\Plugin\Context\InstallContext;
use Shopware\Components\Plugin\Context\UninstallContext;
use Shopware\Components\Plugin\Context\UpdateContext;
use Wuunder\Models\WuunderShipment;


class Wuunder extends Plugin
{
    public static function getSubscribedEvents()
    {
        return [
            'Enlight_Controller_Front_StartDispatch' => 'onStartDispatch',
            'Enlight_Controller_Action_PostDispatchSecure_Backend_Order' => 'onPostDispatch',
            'Enlight_Controller_Action_PostDispatch_Backend_Index' => 'onPostDispatchBackendIndex',
        ];
    }

    /**
     * Compare versions.
     * @param string $version Like: 5.0.0
     * @param string $operator Like: <=
     *
     * @return mixed
     */
    public function versionCompare($version, $operator)
    {
        // return by default version compare
        return version_compare(Shopware()->Config()->get('Version'), $version, $operator);
    }

    /**
     * Loads composer dependencies.
     */
    public function onStartDispatch()
    {
        require_once $this->getPath() . '/vendor/autoload.php';
    }

    public function registerMyComponents()
    {
        require_once $this->Path() . '/vendor/autoload.php';
    }

    public function onPostDispatchBackendIndex(Enlight_Event_EventArgs $args)
    {
        /** @var \Enlight_Controller_Action $controller */
        $controller = $args->getSubject();
        $view = $controller->View();
        $request = $controller->Request();

        if ($view->hasTemplate()) {
            $view->addTemplateDir(__DIR__ . '/Resources/views/');
            $view->extendsTemplate('backend/wuunder_module/header.tpl');
        }
        if ($request->getActionName() === 'load') {
            $view->addTemplateDir(__DIR__ . '/Views');
            $view->extendsTemplate('backend/wuunder/view/listener.js');
        }
    }

    public function onPostDispatch(Enlight_Event_EventArgs $args)
    {
        /** @var \Enlight_Controller_Action $controller */
        $controller = $args->getSubject();
        $view = $controller->View();
        $request = $controller->Request();

        $view->addTemplateDir(__DIR__ . '/Views');

        // Extend templates
        if ($request->getActionName() == 'load') {
            $view->extendsTemplate('backend/wuunder/view/list.js');
            $view->extendsTemplate('backend/wuunder/controller/list.js');
        }

        if ($request->getActionName() === 'getList') {
            $assignedData = $view->getAssign('data');

            foreach ($assignedData as $key => $order) {
                $data = $this->getShipmentData(intval($assignedData[$key]['id']));
                $assignedData[$key]["wuunderShipmentData"] = json_encode($data);
            }
            $view->data = $assignedData;
        }
    }

    public function install(InstallContext $context)
    {
        parent::install($context);
        //Register models namespace
        /** @var \Enlight_Loader $loader */
        $loader = $this->container->get('loader');
        $loader->registerNamespace('Wuunder\Models', $this->getPath() . "Models");

        //Setup models in schema
        /** @var EntityManager $models */
        $models = $this->container->get('models');
        $meta_data[] = $models->getClassMetadata('Wuunder\Models\WuunderShipment');
        $schema_tool = new SchemaTool($models);

        //Drop schema
        try {
            $schema_tool->dropSchema($meta_data);
        } catch (\Exception $e) { /* Ignore Exception*/
        }

        $schema_tool->createSchema($meta_data);

        $this->installAttributes();
    }

    /**
     * install new basket/order attributes
     * @return multitype:boolean multitype:string
     */
    public function installAttributes()
    {
        if ($this->versionCompare('5.5.0', '>=')) {
            $service = $this->container->get('shopware_attribute.crud_service');
            $service->update('s_order_basket_attributes', 'wuunderconnector_wuunder_parcelshop_id', 'string');
            $service->update('s_order_details_attributes', 'wuunderconnector_wuunder_parcelshop_id', 'string');
        } else {
            Shopware()->Models()->addAttribute(
                's_order_basket_attributes',
                'wuunderconnector',
                'wuunder_parcelshop_id',
                'VARCHAR(255)',
                true,
                null);
            Shopware()->Models()->addAttribute(
                's_order_details_attributes',
                'wuunderconnector',
                'wuunder_parcelshop_id',
                'VARCHAR(255)',
                true,
                null);
        }

        $metaDataCacheDoctrine = Shopware()->Models()->getConfiguration()->getMetadataCacheImpl();
        $metaDataCacheDoctrine->deleteAll();

        Shopware()->Models()->generateAttributeModels(array('s_order_basket_attributes'));
        Shopware()->Models()->generateAttributeModels(array('s_order_details_attributes'));
    }


    /**
     * uninstall new basket/order attributes
     * @return multitype:boolean multitype:string
     */
    public function uninstallAttributes()
    {
        if ($this->versionCompare('5.5.0', '>=')) {
            $service = $this->container->get('shopware_attribute.crud_service');
            $service->delete('s_order_basket_attributes', 'wuunderconnector_wuunder_parcelshop_id');
            $service->delete('s_order_details_attributes', 'wuunderconnector_wuunder_parcelshop_id');
        } else {
            Shopware()->Models()->removeAttribute(
                's_order_basket_attributes',
                'wuunderconnector',
                'wuunder_parcelshop_id');
            Shopware()->Models()->addAttribute(
                's_order_details_attributes',
                'wuunderconnector',
                'wuunder_parcelshop_id');
        }

        $metaDataCacheDoctrine = Shopware()->Models()->getConfiguration()->getMetadataCacheImpl();
        $metaDataCacheDoctrine->deleteAll();

        Shopware()->Models()->generateAttributeModels(array('s_order_basket_attributes'));
        Shopware()->Models()->generateAttributeModels(array('s_order_details_attributes'));
    }


    public function activate(ActivateContext $context)
    {
        parent::activate($context); // TODO: Change the autogenerated stub
    }

    public function deactivate(DeactivateContext $context)
    {
        parent::deactivate($context); // TODO: Change the autogenerated stub
    }

    public function update(UpdateContext $context)
    {
        parent::update($context); // TODO: Change the autogenerated stub
    }

    function uninstall(UninstallContext $context)
    {
        parent::uninstall($context);
        //Setup models in schema
        /** @var EntityManager $models */
        $models = $this->container->get('models');
        $meta_data[] = $models->getClassMetadata('Wuunder\Models\WuunderShipment');
        $schema_tool = new SchemaTool($models);

        //Drop schema
        try {
            $schema_tool->dropSchema($meta_data);
        } catch (\Exception $e) { /* Ignore Exception*/
        }

        try {
            $this->uninstallAttributes();
        } catch (\Exception $e) { /* Ignore Exception*/
        }
    }

    private function getShipmentData($order_id)
    {

        /** @var EntityManager $em */
        $em = $this->container->get('models');
        $shipment_repo = $em->getRepository('Wuunder\Models\WuunderShipment');
        $shipments = $shipment_repo->findBy(['order_id' => $order_id]);
        $shipments = array_map(function (WuunderShipment $shipment) {
            return $shipment->getData();
        }, $shipments);

        return $shipments[0];
    }
}
