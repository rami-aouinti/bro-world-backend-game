<?php

declare(strict_types=1);

namespace App\Configuration\Application\Service;

use App\Configuration\Domain\Entity\Enum\FlagType;

/**
 * @package Workplacenow\ConfigService\Service
 * @author  Rami Aouinti <rami.aouinti@tkdeutschland.de>
 */
class ConfigurationService
{
    /**
     * @param array<string> $flags
     */
    public function updateConfig(string $context, array $flags): bool
    {
        if (in_array(FlagType::PROTECTED_SYSTEM->value, $flags) && $context !== 'system') {
            return false;
        }

        if (in_array(FlagType::PROTECTED_WORKPLACE->value, $flags) && !in_array($context, ['system', 'workplace'])) {
            return false;
        }

        return true;
    }
}
