<?php

namespace App\Twig;

use App\Service\SiteContextBuilder;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class SiteContextExtension extends AbstractExtension
{
    public function __construct(private readonly SiteContextBuilder $siteContextBuilder)
    {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('site_context', [$this, 'getSiteContext']),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function getSiteContext(): array
    {
        return $this->siteContextBuilder->build()['site'] ?? [];
    }
}
