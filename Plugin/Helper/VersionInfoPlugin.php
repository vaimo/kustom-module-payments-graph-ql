<?php
/**
 * Copyright © Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
declare(strict_types=1);

namespace Klarna\KpGraphQl\Plugin\Helper;

use Klarna\Base\Helper\VersionInfo;

/**
 * @internal
 */
class VersionInfoPlugin
{
    /**
     * Adds own module name and version
     *
     * @param VersionInfo $subject
     * @param string $result
     * @param string $version
     * @param string $caller
     * @return string
     * @SuppressWarnings(PMD.UnusedFormalParameter)
     */
    public function afterGetModuleVersionString(
        VersionInfo $subject,
        string $result,
        string $version,
        string $caller
    ): string {
        return sprintf(
            "%s;GraphQl/%s",
            $result,
            $subject->getVersion('Klarna_KpGraphQl')
        );
    }
}
