<?php
/**
 * Copyright © Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
declare(strict_types=1);

namespace Klarna\KpGraphQl\Test\Unit\Model\Resolver;

use Klarna\Kp\Model\Configuration\ApiValidation;
use Klarna\KpGraphQl\Model\Resolver\Validation;
use Magento\Store\Model\Store;
use Klarna\Base\Test\Unit\Mock\TestCase;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;

/**
 * @coversDefaultClass \Klarna\KpGraphQl\Model\Resolver\Validation
 */
class ValidationTest extends TestCase
{
    /**
     * @var Validation
     */
    private Validation $validation;
    /**
     * @var ApiValidation
     */
    private ApiValidation $apiValidation;
    /**
     * @var Store
     */
    private Store $store;
    /**
     * @var string $maskedCartId
     */
    private string $maskedCartId = 'mockedMaskedCartId';

    public function testCanRequestResolvedWithNoErrors(): void
    {
        $this->dependencyMocks['apiValidation']->method('isKpEnabled')
            ->with($this->store)
            ->willReturn(true);
        static::assertEquals(null, $this->validation->canRequestResolved($this->maskedCartId, $this->store));
    }

    public function testCanRequestResolvedMissingMaskedCartID(): void
    {
        $this->dependencyMocks['apiValidation']->method('isKpEnabled')
            ->with($this->store)
            ->willReturn(true);
        $this->expectException(GraphQlInputException::class);
        $this->expectExceptionMessage("Required parameter 'cart_id' is missing");
        $this->validation->canRequestResolved('', $this->store);
    }

    public function testCanRequestResolvedMissingStore(): void
    {
        $this->expectException(GraphQlInputException::class);
        $this->expectExceptionMessage("Klarna Payments method is not active");
        $this->validation->canRequestResolved($this->maskedCartId, $this->store);
    }

    protected function setUp(): void
    {
        $this->validation = parent::setUpMocks(Validation::class);
        
        $this->apiValidation = $this->mockFactory->create(ApiValidation::class);
        $this->store = $this->mockFactory->create(Store::class);
   }
}