<?php
class OrderConfirmationController extends OrderConfirmationControllerCore
{
    public function initContent()
    {
        FrontController::initContent();

        $this->context->smarty->assign(array(
            'is_guest' => $this->context->customer->is_guest,
            'HOOK_ORDER_CONFIRMATION' => $this->displayOrderConfirmation(),
            'HOOK_PAYMENT_RETURN' => $this->displayPaymentReturn()
        ));

        if ($this->context->customer->is_guest) {
            $this->context->smarty->assign(array(
                'id_order' => $this->id_order,
                'reference_order' => $this->reference,
                'id_order_formatted' => sprintf('#%06d', $this->id_order),
                'email' => $this->context->customer->email
            ));
            /* If guest we clear the cookie for security reason */
            $this->context->customer->mylogout();
        }

		$order = new Order($this->id_order);

		if (Validate::isLoadedObject($order)) {
			
			$this->context->smarty->assign(array(
                'total_paid' => $order->getOrdersTotalPaid()
            ));
		}
		
        $this->setTemplate(_PS_THEME_DIR_.'order-confirmation.tpl');
    }
}
