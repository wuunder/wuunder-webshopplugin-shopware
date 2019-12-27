<?php

namespace Wuunder\Subscriber;

use Enlight\Event\SubscriberInterface;
use Enlight_Template_Manager;
use Shopware\Components\Plugin\ConfigReader;
use Shopware\Models\Order\Order;

class RouteSubscriber implements SubscriberInterface
{
    private $pluginDirectory;

    public static function getSubscribedEvents()
    {
        return [
            'sBasket::sGetBasket::after' => 'storeBasketResultToSession',
            'Enlight_Controller_Action_PostDispatchSecure_Frontend_Checkout' => 'onCheckout',
            'Enlight_Controller_Action_PreDispatch_Frontend_Checkout' => 'onSaveShipping',
            'Shopware_Controllers_Frontend_Checkout::finishAction::replace' => 'onCheckoutFinish'
        ];
    }

    public function __construct($pluginName, $pluginDirectory, ConfigReader $configReader)
    {
        $this->pluginDirectory = $pluginDirectory;
        $this->config = $configReader->getByPluginName($pluginName);
    }

    public function onCheckout(\Enlight_Controller_ActionEventArgs $args)
    {
        /** @var \Enlight_Controller_Action $controller */
        $controller = $args->get('subject');
        $config = $controller
            ->get('shopware.plugin.config_reader')
            ->getByPluginName('Wuunder');

        if ((int)$config['parcelshop_method_enabled']) {
            $request = $controller->Request();
            $action = $request->getActionName();
            if ($action === 'shippingPayment') {
                $view = $controller->View();
                $view->addTemplateDir($this->pluginDirectory . '/Resources/views');
            }
        }
    }

    public function onSaveShipping(\Enlight_Controller_ActionEventArgs $args)
    {
        /** @var \Enlight_Controller_Action $controller */
        $controller = $args->get('subject');
        $config = $controller
            ->get('shopware.plugin.config_reader')
            ->getByPluginName('Wuunder');

        if ((int)$config['parcelshop_method_enabled']) {
            $request = $controller->Request();
            $action = $request->getActionName();
            if (($action === 'saveShippingPayment' && !$controller->Request()->getParam('isXHR'))) {
                $dispatch = $controller->Request()->getPost('sDispatch');
                $config = $controller
                    ->get('shopware.plugin.config_reader')
                    ->getByPluginName('Wuunder');
                $ourDispatch = (int)$config['parcelshop_method']; //TODO Make Dynamic

                $basket = Shopware()->Session()->connectGetBasket;
                $basketId = $basket['content'][0]['id'];

                $entityManager = $this->getEntityManager();
                $basket_repo = $entityManager->getRepository(\Shopware\Models\Order\Basket::class);
                $basket = $basket_repo->find($basketId);

                $attribute = $basket->getAttribute();
                $parcelshopId = $attribute->getWuunderconnectorWuunderParcelshopId();

                if ($dispatch == $ourDispatch && empty($parcelshopId)) {
                    $sErrorFlag['sDispatch'] = true;
                    $controller->View()->assign('wuunderParcelshopError', "You need to select a parcelshop before continuing", null, Enlight_Template_Manager::SCOPE_ROOT);
                    return $controller->forward('shippingPayment');
                }
            }
        }
    }

    public function onCheckoutFinish(\Enlight_Event_EventArgs $args)
    {
        /** @var \Enlight_Controller_Action $controller */
        $controller = $args->getSubject();
        $config = $controller
            ->get('shopware.plugin.config_reader')
            ->getByPluginName('Wuunder');

        if ((int)$config['parcelshop_method_enabled']) {
            $request = $controller->Request();

            $action = $request->getActionName();
            if ($action === 'finish') {
                $dispatch = Shopware()->Session()['sDispatch'];
                $ourDispatch = 18; //TODO Make Dynamic

                if ($dispatch == $ourDispatch) {
                    $basket = Shopware()->Session()->connectGetBasket;
                    $basketId = $basket['content'][0]['id'];

                    $entityManager = $this->getEntityManager();
                    $basket_repo = $entityManager->getRepository(\Shopware\Models\Order\Basket::class);
                    $basket = $basket_repo->find($basketId);

                    if ($basket) {
                        $attribute = $basket->getAttribute();
                        $parcelshopId = $attribute->getWuunderconnectorWuunderParcelshopId();

                        if ($dispatch == $ourDispatch && empty($parcelshopId)) {
                            $sErrorFlag['sDispatch'] = true;
                            $controller->View()->assign('wuunderParcelshopError', "You need to select a parcelshop before continuing", null, Enlight_Template_Manager::SCOPE_ROOT);

                            return $controller->redirect([
                                'controller' => 'checkout',
                                'action' => 'shippingPayment',
                            ]);
                        }
                    }
                }
            }
        }
        $args->setReturn(
            $args->getSubject()->executeParent(
                $args->getMethod(),
                $args->getArgs()
            )
        );
    }

    /**
     * returns shopware model manager
     * @return Ambigous <\Shopware\Components\Model\ModelManager, mixed, multitype:>
     */
    public function getEntityManager()
    {
        return Shopware()->Models();
    }

    /**
     * Hook for the sGetBasket method, which will store the most recent basket to the session
     *
     * @event sBasket::sGetBasket::after
     * @param \Enlight_Hook_HookArgs $args
     */
    public function storeBasketResultToSession(\Enlight_Hook_HookArgs $args)
    {
        $basket = $args->getReturn();
        Shopware()->Session()->connectGetBasket = $basket;
        $args->setReturn($basket);
    }
}
