<?php

namespace Wuunder\Subscriber;

use Enlight\Event\SubscriberInterface;
use Enlight_Template_Manager;
use Shopware\Components\Logger;
use Shopware\Components\Plugin\ConfigReader;
use Shopware\Models\Order\Order;

class RouteSubscriber implements SubscriberInterface
{
    private $pluginDirectory;

    private $logger;

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
                $ourDispatch = (int)$config['parcelshop_method'];

                $basket = Shopware()->Session()->connectGetBasket;
                $basketId = $basket['content'][0]['id'];

                $entityManager = $this->getEntityManager();
                $basket_repo = $entityManager->getRepository('Shopware\Models\Order\Basket');
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
                $config = $controller
                    ->get('shopware.plugin.config_reader')
                    ->getByPluginName('Wuunder');
                $ourDispatch = (int)$config['parcelshop_method'];

                if ($dispatch == $ourDispatch) {
                    $basket = Shopware()->Session()->connectGetBasket;
                    $this->logger->addError('Basket:' . json_encode($basket));
                    if ($basket && isset($basket['content']) && isset($basket['content'][0]) && isset($basket['content'][0]['id'])) {

                        $basketId = $basket['content'][0]['id'];

                        $entityManager = $this->getEntityManager();
                        $basket_repo = $entityManager->getRepository('Shopware\Models\Order\Basket');
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
                    } else {
                        $this->logger->addError('problematic basket:' . json_encode($basket));
                    }
                } else {
                    $basket = Shopware()->Session()->connectGetBasket;
                    //log basket
                    $this->logger->addError('Basket:' . json_encode($basket));
                    if ($basket && isset($basket['content']) && isset($basket['content'][0]) && isset($basket['content'][0]['id'])) {
                        $basketId = $basket['content'][0]['id'];
                        $entityManager = $this->getEntityManager();
                        $basket_repo = $entityManager->getRepository('Shopware\Models\Order\Basket');
                        $basket = $basket_repo->find($basketId);
                    
                        if ($basket) {
                            $attribute = $basket->getAttribute();
                            $attribute->setWuunderconnectorWuunderParcelshopId(null);

                            $basket->setAttribute($attribute);
                            $entityManager->persist($basket);
                            $entityManager->flush();
                        }
                    } else {
                        $this->logger->addError('problematic basket:' . json_encode($basket));
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
