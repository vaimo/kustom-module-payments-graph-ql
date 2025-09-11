<?php
/**
 * Copyright © Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
declare(strict_types=1);

namespace Klarna\KpGraphQl\Plugin\Model\Resolver;

use Klarna\Kp\Model\Configuration\ApiValidation;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\QuoteGraphQl\Model\Resolver\AvailablePaymentMethods;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Store\Model\StoreManagerInterface;
use Klarna\Kp\Model\QuoteRepository;

/**
 * @internal
 */
class AvailablePaymentMethodsPlugin
{
    /**
     * @var StoreManagerInterface
     */
    private StoreManagerInterface $storeManager;
    /**
     * @var QuoteRepository
     */
    private QuoteRepository $kQuoteRepository;
    /**
     * @var ApiValidation
     */
    private ApiValidation $apiValidation;

    /**
     * @param StoreManagerInterface $storeManager
     * @param QuoteRepository $kQuoteRepository
     * @param ApiValidation $apiValidation
     * @codeCoverageIgnore
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        QuoteRepository $kQuoteRepository,
        ApiValidation $apiValidation
    ) {
        $this->storeManager = $storeManager;
        $this->kQuoteRepository = $kQuoteRepository;
        $this->apiValidation = $apiValidation;
    }

    /**
     * Modify results of resolve() call to apply the dynamic title for Klarna methods returned by API
     *
     * @param AvailablePaymentMethods $subject
     * @param array                   $list
     * @param Field                   $field
     * @param ContextInterface        $context
     * @param ResolveInfo             $info
     * @param array                   $value
     * @return array
     * @throws NoSuchEntityException
     * @SuppressWarnings(PMD.UnusedFormalParameter)
     */
    public function afterResolve(
        AvailablePaymentMethods $subject,
        array                   $list,
        Field                   $field,
        ContextInterface        $context,
        ResolveInfo             $info,
        array                   $value
    ): array {
        if (!$this->apiValidation->isKpEnabled($this->storeManager->getStore())) {
            return $list;
        }
        /** @var CartInterface $cart */
        $cart = $value['model'];
        try {
            $klarnaQuote = $this->kQuoteRepository->getActiveByQuoteId($cart->getId());
        } catch (\Exception $e) {
            return $list;
        }
        $paymentCategories = json_decode(json_encode($klarnaQuote->getPaymentMethodInfo()), true);
        $paymentCategories = array_map(function ($paymentCategory) {
            return [
                'title' => $paymentCategory['name'],
                'code'  => 'klarna_' . $paymentCategory['identifier'],
            ];
        }, $paymentCategories);
        $list              = array_reverse(array_merge($list, $paymentCategories));
        $newList           = [];
        foreach ($list as $method) {
            if (!in_array($method['code'], array_column($newList, 'code'))) {
                $newList[] = $method;
            }
        }

        return $newList;
    }
}
