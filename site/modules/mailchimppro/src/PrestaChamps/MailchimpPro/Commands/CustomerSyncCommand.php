<?php
/**
 * PrestaChamps
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Commercial License
 * you can't distribute, modify or sell this code
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file
 * If you need help please contact leo@prestachamps.com
 *
 * @author    Mailchimp
 * @copyright Mailchimp
 * @license   commercial
 */

namespace PrestaChamps\MailchimpPro\Commands;

use Context;
use Customer;
use CustomerCore;
use DrewM\MailChimp\MailChimp;
use PrestaChamps\MailchimpPro\Formatters\CustomerFormatter;
use PrestaChamps\MailchimpPro\Formatters\ListMemberFormatter;
use PrestaShopDatabaseException;
use Tools;

/**
 * Class CustomerSyncCommand
 *
 * @package PrestaChamps\MailchimpPro\Commands
 */
class CustomerSyncCommand extends BaseApiCommand
{
    protected $context;
    protected $customerIds;
    protected $mailchimp;
    protected $batch;
    protected $batchPrefix = '';
    protected $triggerDoubleOptIn = true;

    /**
     * ProductSyncService constructor.
     *
     * @param Context $context
     * @param MailChimp $mailchimp
     * @param array $customerIds
     */
    public function __construct(Context $context, MailChimp $mailchimp, $customerIds = array())
    {
        $this->context = $context;
        $this->mailchimp = $mailchimp;
        $this->batchPrefix = uniqid('CUSTOMER_SYNC', true);
        $this->batch = $this->mailchimp->new_batch($this->batchPrefix);
        $this->customerIds = $customerIds;
    }

    /**
     * Trigger DoubleOptIn feature
     *
     * @param bool $trigger
     */
    public function triggerDoubleOptIn($trigger = true)
    {
        $this->triggerDoubleOptIn = (bool)$trigger;
    }

    /**
     * @return array
     * @throws PrestaShopDatabaseException
     */
    public function execute()
    {
        $this->responses = array();
        if ((int)$this->syncMode === self::SYNC_MODE_REGULAR) {
            $listId = $this->getListIdFromStore();
            $listRequiresDoi = $this->getListRequiresDOI($listId);
            foreach ($this->customerIds as $customerId) {
                $customer = new Customer($customerId);
                $formatted = new CustomerFormatter($customer, $this->context);
                if ($this->method === self::SYNC_METHOD_POST) {
                    $data = $formatted->format();
                    /**
                     * @var $customer CustomerCore
                     */
                    $listMemberFormatter = new ListMemberFormatter(
                        $customer,
                        $this->context,
                        $this->getMemberNewsletterStatus($customer, $listRequiresDoi),
                        ListMemberFormatter::EMAIL_TYPE_HTML
                    );
                    
                    $data['opt_in_status'] = ($customer->newsletter == '1') ? true : false;
                    $this->mailchimp->put(
                        "/ecommerce/stores/{$this->context->shop->id}/customers/{$customerId}",
                        $data
                    );
                    $hash = md5(Tools::strtolower($customer->email));
                    $this->mailchimp->put("/lists/{$listId}/members/{$hash}", $listMemberFormatter->format());
                }
                if ($this->method === self::SYNC_METHOD_PATCH) {
                    $data = $formatted->format();
                    $this->mailchimp->put(
                        "/ecommerce/stores/{$this->context->shop->id}/customers/{$customerId}",
                        $data
                    );
                }
                if ($this->method === self::SYNC_METHOD_DELETE) {
                    $this->mailchimp->delete(
                        "/ecommerce/stores/{$this->context->shop->id}/customers/{$customerId}"
                    );
                }
                $this->responses[] = $this->mailchimp->getLastResponse();
            }
        }

        if ((int)$this->syncMode === self::SYNC_MODE_BATCH) {
            $batch = $this->mailchimp->new_batch();
            foreach ($this->customerIds as $customerId) {
                $formatted = new CustomerFormatter(new Customer($customerId), $this->context);
                if ($this->method === 'POST') {
                    $batch->put(
                        "{$this->batchPrefix}_{$customerId}",
                        "/ecommerce/stores/{$this->context->shop->id}/customers/{$customerId}",
                        $formatted->format()
                    );
                }
                if ($this->method === 'PATCH') {
                    $data = $formatted->format();
                    $batch->put(
                        "{$this->batchPrefix}_{$customerId}",
                        "/ecommerce/stores/{$this->context->shop->id}/customers/{$customerId}",
                        $data
                    );
                }
                if ($this->method === 'DELETE') {
                    $batch->delete(
                        "{$this->batchPrefix}_{$customerId}",
                        "/ecommerce/stores/{$this->context->shop->id}/customers/{$customerId}"
                    );
                }
                $this->responses[] = $this->mailchimp->getLastResponse();
            }
            $this->responses[] = $batch->execute();
        }

        return $this->responses;
    }

    /**
     * @param Customer $customer
     * @param bool $listRequiresDoi
     * @return string
     */
    public function getMemberNewsletterStatus(Customer $customer, $listRequiresDoi)
    {
        if (!$customer->newsletter) {
            return ListMemberFormatter::STATUS_TRANSACTIONAL;
        }
        if ($listRequiresDoi && $customer->newsletter) {
            return ListMemberFormatter::STATUS_PENDING;
        }

        if (!$listRequiresDoi && $customer->newsletter) {
            return ListMemberFormatter::STATUS_SUBSCRIBED;
        }

        return ListMemberFormatter::STATUS_TRANSACTIONAL;
    }
}
