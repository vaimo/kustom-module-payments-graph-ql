<?php
/**
 * Copyright © Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */

namespace Klarna\KpGraphQl\Test\Unit\Plugin\Model\Resolver;

use Klarna\KpGraphQl\Plugin\Model\Resolver\AvailablePaymentMethodsPlugin;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\GraphQl\Model\Query\Context;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use PHPUnit\Framework\MockObject\MockObject;
use Klarna\Base\Test\Unit\Mock\TestCase;
use Klarna\Kp\Model\Quote;
use Magento\QuoteGraphQl\Model\Resolver\AvailablePaymentMethods;
use Magento\Store\Model\Store;
use Magento\Quote\Api\Data\CartInterface;

/**
 * @coversDefaultClass \Klarna\KpGraphQl\Plugin\Model\Resolver\AvailablePaymentMethodsPlugin
 */
class AvailablePaymentMethodsPluginTest extends TestCase
{
    /**
     * @var AvailablePaymentMethodsPlugin
     */
    private $availablePaymentMethodsPlugin;
    /**
     * @var AvailablePaymentMethods
     */
    private $availablePaymentMethods;
    /**
     * @var Field
     */
    private $field;
    /**
     * @var Context
     */
    private $context;
    /**
     * @var ResolveInfo
     */
    private $resolveInfo;
    /**
     * @var array
     */
    private $value;

    /**
     * @covers ::afterResolve
     */
    public function testAfterResolveReturnsDefaultListWhenKlarnaIsDisabled(): void
    {
        $list = [
            'title' => 'Check Money Order',
            'code'  => 'checkmo',
        ];

        $this->dependencyMocks['apiValidation']->method('isKpEnabled')->willReturn(false);

        $result = $this->availablePaymentMethodsPlugin->afterResolve(
            $this->availablePaymentMethods,
            $list,
            $this->field,
            $this->context,
            $this->resolveInfo,
            $this->value
        );

        self::assertSame($list, $result);
    }

    /**
     * @covers ::afterResolve
     */
    public function testAfterResolveReturnsDefaultListWithoutKlarnaQuote(): void
    {
        $list = [
            'title' => 'Check Money Order',
            'code'  => 'checkmo',
        ];

        $this->dependencyMocks['apiValidation']->method('isKpEnabled')->willReturn(true);
        $this->dependencyMocks['kQuoteRepository']->method('getActiveByQuoteId')->willThrowException(
            new NoSuchEntityException()
        );

        $result = $this->availablePaymentMethodsPlugin->afterResolve(
            $this->availablePaymentMethods,
            $list,
            $this->field,
            $this->context,
            $this->resolveInfo,
            $this->value
        );

        self::assertSame($list, $result);
    }

    /**
     * @covers ::afterResolve
     */
    public function testAfterResolveReturnsListWithKlarnaMethods(): void
    {
        $list[] = [
            'title' => 'Check Money Order',
            'code'  => 'checkmo',
        ];

        $klarnaMethod[] = [
            'identifier' => 'pay_later',
            'name'       => 'Rechnung',
        ];

        $newList = [
            [
                'title' => 'Rechnung',
                'code'  => 'klarna_pay_later',
            ],
            [
                'title' => 'Check Money Order',
                'code'  => 'checkmo',
            ],
        ];

        $this->dependencyMocks['apiValidation']->method('isKpEnabled')->willReturn(true);
        $quote = $this->mockFactory->create(Quote::class);
        $quote->method('getPaymentMethodInfo')->willReturn($klarnaMethod);
        $this->dependencyMocks['kQuoteRepository']->method('getActiveByQuoteId')->willReturn($quote);

        $result = $this->availablePaymentMethodsPlugin->afterResolve(
            $this->availablePaymentMethods,
            $list,
            $this->field,
            $this->context,
            $this->resolveInfo,
            $this->value
        );

        self::assertSame($newList, $result);
    }

    protected function setUp(): void
    {
        $this->availablePaymentMethodsPlugin = parent::setUpMocks(AvailablePaymentMethodsPlugin::class);

        $this->availablePaymentMethods       = $this->mockFactory->create(AvailablePaymentMethods::class);
        $this->field                         = $this->mockFactory->create(Field::class);
        $this->context                       = $this->mockFactory->create(Context::class);
        $this->resolveInfo                   = $this->mockFactory->create(ResolveInfo::class);
        $this->value['model']                = $this->mockFactory->create(CartInterface::class);
        $this->value['model']->method('getId')
            ->willReturn('1');

        $store                               = $this->mockFactory->create(Store::class);
        $this->dependencyMocks['storeManager']->method('getStore')->willReturn($store);
    }
}
