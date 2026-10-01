<?php

namespace App\Twig;

use App\Entity\Product;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

/**
 * Aides pour les balises SEO (title, meta description, JSON-LD).
 *
 *  {{ texte|seo_text }}            HTML → texte brut (espaces entre les blocs, entités décodées)
 *  {{ texte|seo_truncate(155) }}   coupe au dernier mot entier + « … »
 *  {{ product_seo_title(product) }} titre de fiche produit (sans le suffixe « | ADIVEC »)
 */
class SeoExtension extends AbstractExtension
{
    /** Longueur visée pour le contenu de <title> hors suffixe « | ADIVEC » (9 caractères). */
    public const TITLE_MAX = 56;

    public function getFilters(): array
    {
        return [
            new TwigFilter('seo_text', [$this, 'text']),
            new TwigFilter('seo_truncate', [$this, 'truncate']),
        ];
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('product_seo_title', [$this, 'productTitle']),
        ];
    }

    public function text(?string $html): string
    {
        // Espace avant chaque balise : évite « interrogeAvec » quand deux paragraphes se suivent
        $text = strip_tags(str_replace('<', ' <', (string) $html));
        $text = html_entity_decode($text, \ENT_QUOTES | \ENT_HTML5, 'UTF-8');

        return trim(preg_replace('/\s+/u', ' ', $text));
    }

    public function truncate(?string $text, int $length = 155, string $ellipsis = '…'): string
    {
        $text = trim((string) $text);
        if (mb_strlen($text) <= $length) {
            return $text;
        }
        $cut = mb_substr($text, 0, $length - mb_strlen($ellipsis) + 1);
        // Recul jusqu'au dernier espace pour ne pas couper un mot
        $space = mb_strrpos($cut, ' ');
        if ($space !== false && $space > $length / 2) {
            $cut = mb_substr($cut, 0, $space);
        } else {
            $cut = mb_substr($cut, 0, $length - mb_strlen($ellipsis));
        }

        return rtrim($cut, " ,;:–-") . $ellipsis;
    }

    /**
     * « Gomme de guar – Gélifiants - Epaississants » : le type distingue les produits homonymes
     * (sauce soja liquide / déshydratée…) et allonge les titres trop courts.
     */
    public function productTitle(Product $product): string
    {
        $name = trim((string) $product->getNom());
        $type = trim((string) $product->getType()?->getName());
        $family = (string) $product->getType()?->getFamily()?->getName();

        $normalize = static fn (string $s): string => mb_strtolower(rtrim($s, 'sx'));
        $typeIsRedundant = $type === ''
            || str_contains($normalize($type), $normalize($name))
            || str_contains($normalize($name), $normalize($type));

        $qualifier = $typeIsRedundant
            ? ($family === 'Technique' ? 'ingrédient naturel technique' : 'ingrédient naturel')
            : $type;

        $title = $name . ' – ' . $qualifier;
        if (mb_strlen($title) <= self::TITLE_MAX) {
            return $title;
        }

        return mb_strlen($name) <= self::TITLE_MAX ? $name : $this->truncate($name, self::TITLE_MAX);
    }
}
