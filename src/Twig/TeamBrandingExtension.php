<?php

namespace App\Twig;

use App\Service\TeamBranding;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class TeamBrandingExtension extends AbstractExtension
{
    public function __construct(private readonly TeamBranding $teamBranding)
    {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('team_logo', [$this, 'getTeamLogo']),
        ];
    }

    public function getTeamLogo(?string $teamName): string
    {
        return $this->teamBranding->getLogoPath($teamName);
    }
}
