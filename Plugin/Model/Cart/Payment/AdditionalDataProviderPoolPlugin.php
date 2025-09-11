<?php
/**
 * Copyright © Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
declare(strict_types=1);

namespace Klarna\KpGraphQl\Plugin\Model\Cart\Payment;

use Klarna\Base\Model\Payment\EnablementChecker;
use Magento\QuoteGraphQl\Model\Cart\Payment\AdditionalDataProviderPool;

/**
 * Plugin for AdditionalDataProvider to allow usage of dynamic methods
 *
 * @internal
 */
class AdditionalDataProviderPoolPlugin
{
    /**
     * @var EnablementChecker
     */
    private EnablementChecker $enablementChecker;

    /**
     * @param EnablementChecker $enablementChecker
     * @codeCoverageIgnore
     */
    public function __construct(EnablementChecker $enablementChecker)
    {
        $this->enablementChecker = $enablementChecker;
    }

    /**
     * Modify results of getData() call to add in Klarna methods returned by API
     *
     * @param AdditionalDataProviderPool $subject
     * @param string $paymentCode
     * @param array $data
     * @return array
     * @SuppressWarnings(PMD.UnusedFormalParameter)
     */
    public function beforeGetData(AdditionalDataProviderPool $subject, string $paymentCode, array $data): array
    {
        if ($this->enablementChecker->isPaymentMethodCodeStartsWithKlarna($paymentCode)) {
            $data[$paymentCode] = $data['klarna'];
            $paymentCode        = 'klarna';
            unset($data['klarna']);
            return [$paymentCode, $data];
        }
        return [$paymentCode, $data];
    }
}
