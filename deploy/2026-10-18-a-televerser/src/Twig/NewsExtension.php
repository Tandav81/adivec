<?php

namespace App\Twig;

use App\Service\NewsHtmlFormatter;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

/**
 * Affichage des actualités.
 *
 *  {{ new.description|news_html }}     HTML Trix restructuré (p, h2, h3, listes) — voir NewsHtmlFormatter
 *  {{ new.description|reading_time }}  temps de lecture en minutes (200 mots/min, minimum 1)
 */
class NewsExtension extends AbstractExtension
{
    private const WORDS_PER_MINUTE = 200;

    public function __construct(private readonly NewsHtmlFormatter $formatter)
    {
    }

    public function getFilters(): array
    {
        return [
            // Contenu saisi dans le back-office (déjà affiché en |raw auparavant)
            new TwigFilter('news_html', [$this->formatter, 'format'], ['is_safe' => ['html']]),
            new TwigFilter('reading_time', [$this, 'readingTime']),
        ];
    }

    public function readingTime(?string $html): int
    {
        $text = html_entity_decode(strip_tags(str_replace('<', ' <', (string) $html)), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $words = preg_match_all('/[\p{L}\p{N}]+/u', $text);

        return max(1, (int) round($words / self::WORDS_PER_MINUTE));
    }
}
