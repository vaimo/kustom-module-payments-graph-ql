<?php
/**
 * Copyright © Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
declare(strict_types=1);

namespace Klarna\KpGraphQl\Model\Resolver;

use Klarna\Kp\Model\Configuration\ApiValidation;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;

/**
 * @internal
 */
class Validation
{
    /**
     * @var ApiValidation
     */
    private ApiValidation $apiValidation;

    /**
     * @param ApiValidation $apiValidation
     * @codeCoverageIgnore
     */
    public function __construct(ApiValidation $apiValidation)
    {
        $this->apiValidation = $apiValidation;
    }

    /**
     * Checking if the request can be resolved
     *
     * @param string $maskedCartId
     * @param StoreInterface $store
     */
    public function canRequestResolved(string $maskedCartId, StoreInterface $store): void
    {
        if (!$maskedCartId) {
            throw new GraphQlInputException(__("Required parameter '%1' is missing", 'cart_id'));
        }
        if (!$this->apiValidation->isKpEnabled($store)) {
            throw new GraphQlInputException(__("Klarna Payments method is not active"));
        }
    }
}
