<?php
/**
 * Copyright © Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */

namespace Klarna\KpGraphQl\Test\Unit\Plugin\Helper;

use Klarna\Base\Helper\VersionInfo;
use Klarna\KpGraphQl\Plugin\Helper\VersionInfoPlugin;
use Klarna\Base\Test\Unit\Mock\TestCase;

/**
 * @coversDefaultClass  \Klarna\KpGraphQl\Plugin\Helper\VersionInfoPlugin
 */
class VersionInfoPluginTest extends TestCase
{
    /**
     * @var VersionInfoPlugin
     */
    private $model;

    /**
     * @covers ::afterGetModuleVersionString()
     */
    public function testAfterGetModuleVersionStringAppendsVersion(): void
    {
        $versionInfo = $this->createMock(VersionInfo::class);
        $versionInfo
            ->method('getVersion')
            ->willReturn('x.y.z');

        $result = $this->model->afterGetModuleVersionString(
            $versionInfo,
            'before',
            '',
            ''
        );

        self::assertSame('before;GraphQl/x.y.z', $result);
    }

    /**
     * Basic setup for test
     */
    protected function setUp(): void
    {
        $this->model = parent::setUpMocks(VersionInfoPlugin::class);
    }
}
