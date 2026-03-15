<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class PageContentExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('render_page_content', [$this, 'renderPageContent'], ['is_safe' => ['html']]),
        ];
    }

    public function renderPageContent(?string $content): string
    {
        if (null === $content || '' === trim($content)) {
            return '';
        }

        $rendered = $content;

        $rendered = preg_replace(
            '#<(div|p)>\s*\[separator\]\s*</\1>#i',
            '<hr class="content-separator">',
            $rendered
        ) ?? $rendered;
        $rendered = str_replace('[separator]', '<hr class="content-separator">', $rendered);

        $rendered = preg_replace_callback(
            '/\[quote\](.*?)\[\/quote\]/is',
            static function (array $matches): string {
                $quote = trim(strip_tags($matches[1]));
                if ('' === $quote) {
                    return '';
                }

                return sprintf(
                    '<blockquote class="content-quote"><p>%s</p></blockquote>',
                    nl2br(htmlspecialchars($quote, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'))
                );
            },
            $rendered
        ) ?? $rendered;

        $rendered = preg_replace_callback(
            '/\[cta\s+([^\]]+)\]/i',
            static function (array $matches): string {
                $attributes = self::parseAttributes($matches[1]);
                $label = trim($attributes['label'] ?? '');
                $url = trim($attributes['url'] ?? '');

                if ('' === $label || '' === $url) {
                    return '';
                }

                return sprintf(
                    '<p class="content-cta-wrap"><a class="button primary content-cta" href="%s">%s</a></p>',
                    htmlspecialchars($url, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'),
                    htmlspecialchars($label, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')
                );
            },
            $rendered
        ) ?? $rendered;

        if ($rendered === strip_tags($rendered)) {
            return '<p>' . nl2br(htmlspecialchars($rendered, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')) . '</p>';
        }

        return $rendered;
    }

    /**
     * @return array<string, string>
     */
    private static function parseAttributes(string $rawAttributes): array
    {
        preg_match_all('/(\w+)="([^"]*)"/', $rawAttributes, $matches, \PREG_SET_ORDER);

        $attributes = [];
        foreach ($matches as $match) {
            $attributes[$match[1]] = $match[2];
        }

        return $attributes;
    }
}
