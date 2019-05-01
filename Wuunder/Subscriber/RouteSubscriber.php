<?php
namespace Wuunder\Subscriber;

use Enlight\Event\SubscriberInterface;
use Shopware\Components\Plugin\ConfigReader;
use Shopware\Models\Order\Order;

class RouteSubscriber implements SubscriberInterface
{
    private $pluginDirectory;

    public static function getSubscribedEvents()
    {
        return [
            'Enlight_Controller_Action_PostDispatchSecure_Frontend_Checkout' => 'onCheckout',
            'Shopware_Modules_Order_SendMail_FilterVariables' => 'onOrdermail',
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
        $request  = $controller->Request();
        $action = $request->getActionName();
        if ($action === 'shippingPayment') {
            $view = $controller->View();
            $view->addTemplateDir($this->pluginDirectory . '/Resources/views');
        }
    }

    public function onOrdermail(\Enlight_Event_EventArgs $args)
    {
        $variables = $args->getReturn();
        $sql = 'UPDATE wuunder_parcelshop SET order_number = ' . $variables['ordernumber'] . ' WHERE user_id = ' . $variables['sOrderDetails'][0]['userID'] . ' AND order_number IS NULL ORDER BY id DESC LIMIT 1;';
        Shopware()->Db()->executeQuery($sql);
    }

    /**
     * returns shopware model manager
     * @return Ambigous <\Shopware\Components\Model\ModelManager, mixed, multitype:>
     */
    public function getEntityManager()
    {
        return Shopware()->Models();
    }
}
