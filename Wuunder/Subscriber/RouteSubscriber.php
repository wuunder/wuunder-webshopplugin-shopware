<?php
namespace Wuunder\Subscriber;

use Enlight\Event\SubscriberInterface;
use Shopware\Components\Plugin\ConfigReader;

class RouteSubscriber implements SubscriberInterface
{
    private $pluginDirectory;

    public static function getSubscribedEvents()
    {
        return [
            //'Enlight_Controller_Action_PostDispatch_Frontend_Checkout' => 'onCheckout',
            'Enlight_Controller_Action_PostDispatch_Frontend' => 'onCheckout',
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
        $view = $controller->View();
        $view->addTemplateDir($this->pluginDirectory . '/Resources/views');
    }
}
