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
use Klarna\Kp\Model\Initialization\Action;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\QuoteGraphQl\Model\Cart\GetCartForUser;
use Magento\Checkout\Model\Session as MagentoSession;
use Magento\Customer\Model\Session as CustomerSession;
use Klarna\Base\Exception as KlarnaException;

/**
 * Resolver for generating Klarna Payments session
 *
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 * @api
 */
class CreateKlarnaPaymentsSession implements ResolverInterface
{
    /**
     * @var GetCartForUser
     */
    private $getCartForUser;
    /**
     * @var RequestInterface
     */
    private $request;
    /**
     * @var Validation
     */
    private Validation $validation;
    /**
     * @var Action
     */
    private Action $action;
    /**
     * @var MagentoSession
     */
    private MagentoSession $magentoSession;
    /**
     * @var CustomerSession
     */
    private CustomerSession $customerSession;
    /**
     * @var ApiValidation
     */
    private ApiValidation $apiValidation;

    /**
     * @param GetCartForUser $getCartForUser
     * @param RequestInterface $request
     * @param Validation $validation
     * @param Action $action
     * @param MagentoSession $magentoSession
     * @param CustomerSession $customerSession
     * @param ApiValidation $apiValidation
     * @codeCoverageIgnore
     */
    public function __construct(
        GetCartForUser $getCartForUser,
        RequestInterface $request,
        Validation $validation,
        Action $action,
        MagentoSession $magentoSession,
        CustomerSession $customerSession,
        ApiValidation $apiValidation
    ) {
        $this->getCartForUser = $getCartForUser;
        $this->request = $request;
        $this->validation = $validation;
        $this->action = $action;
        $this->magentoSession = $magentoSession;
        $this->customerSession = $customerSession;
        $this->apiValidation = $apiValidation;
    }

    /**
     * @inheritdoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        ?array $value = null,
        ?array $args = null
    ) {
        $this->request->setParam('GraphQlCreateSession', true);
        $maskedCartId = $args['input']['cart_id'];
        $disableAuthorizationCallback = $args['input']['disableAuthorizationCallback'] ?? true;
        $store = $context->getExtensionAttributes()->getStore();
        $this->validation->canRequestResolved($maskedCartId, $store);

        $storeId = (int)$store->getId();
        $currentUserId = $context->getUserId();
        $cart = $this->getCartForUser->execute($maskedCartId, $currentUserId, $storeId);

        $this->customerSession->setCustomerId($currentUserId);
        $this->magentoSession->setQuoteId($cart->getId());

        $this->apiValidation->clearFailedValidationHistory();
        if (!$this->apiValidation->sendApiRequestAllowed($cart)) {
            $history = implode(', ', $this->apiValidation->getFailedValidationHistory());
            throw new GraphQlInputException(__('No API request can be sent for Klarna Payments. Reason:' . $history));
        }

        $klarnaQuote = $this->action->sendRequest(
            $cart,
            ['disableAuthorizationCallback' => $disableAuthorizationCallback]
        );
        if (!$klarnaQuote->isActive()) {
            throw new GraphQlInputException(__('Klarna Payments is not available for this cart.'));
        }

        return [
            'client_token'              => $klarnaQuote->getClientToken(),
            'payment_method_categories' => $klarnaQuote->getPaymentMethodInfo()
        ];
    }
}
