<?php
/**
 * Copyright © Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
declare(strict_types=1);

namespace Klarna\KpGraphQl\Model;

use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\QuoteGraphQl\Model\Cart\Payment\AdditionalDataProviderInterface;

/**
 * Check if the authorization_token is available
 *
 * @internal
 */
class KlarnaDataProvider implements AdditionalDataProviderInterface
{
    /**
     * Return Additional Data
     *
     * @param array $args
     * @return array
     * @throws GraphQlInputException
     */
    public function getData(array $args): array
    {
        if (empty($args[$args['code']]['authorization_token'])) {
            throw new GraphQlInputException(__('No authorization token provided!'));
        }
        return $args[$args['code']];
    }
}
