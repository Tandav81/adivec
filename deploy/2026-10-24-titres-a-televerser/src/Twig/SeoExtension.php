<?php

namespace App\Twig;

use App\Entity\Product;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

use function Symfony\Component\String\u;

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
     * Titre de fiche produit, construit par une règle unique (aucun cas particulier) :
     *
     *   Nom (code E) Fabricant – qualificatif
     *
     *  - code E : lu dans la 1re phrase de la description (« … (E412) est … ») ;
     *  - fabricant : celui de la fiche (Kikkoman, Emsland…), recherché par les clients ;
     *  - qualificatif : la catégorie si elle apporte un mot absent du nom (« Colorants naturels »),
     *    sinon « fournisseur agroalimentaire / industriel » (intention B2B), ou sa version courte.
     *
     * Ordre de priorité si le titre dépasse TITLE_MAX : nom > code E > fabricant > qualificatif.
     * Ex. « Gomme de guar (E412) – Gélifiants - Epaississants »,
     *     « Sauce soja standard Kikkoman – agroalimentaire ».
     */
    public function productTitle(Product $product): string
    {
        $name = trim((string) $product->getNom());
        $maker = trim((string) $product->getFournisseur()?->getName());
        $type = trim((string) $product->getType()?->getName());
        $family = (string) $product->getType()?->getFamily()?->getName();
        $code = $this->eCode($product->getDescription());

        $heads = $code !== '' ? [$name . ' (' . $code . ')', $name] : [$name];
        $makers = $maker !== '' && !str_contains(mb_strtolower($name), mb_strtolower($maker)) ? [$maker, ''] : [''];
        $qualifiers = $family === 'Technique'
            ? ['fournisseur industriel', 'usage industriel']
            : ['fournisseur agroalimentaire', 'agroalimentaire'];
        if ($type !== '' && !$this->sharesWord($type, $name)) {
            array_unshift($qualifiers, $type); // la catégorie apporte un mot nouveau
        }
        $qualifiers[] = '';

        foreach ($heads as $head) {
            foreach ($makers as $m) {
                foreach ($qualifiers as $q) {
                    $title = $head . ($m !== '' ? ' ' . $m : '') . ($q !== '' ? ' – ' . $q : '');
                    if (mb_strlen($title) <= self::TITLE_MAX) {
                        return $title;
                    }
                }
            }
        }

        return $this->truncate($name, self::TITLE_MAX);
    }

    /**
     * Code E cité dans la 1re phrase de la description : « E412 » ; « E150 » si plusieurs codes
     * de la même famille (E150a, E150c, E150d) ; '' si aucun ou des codes différents.
     */
    public function eCode(?string $html): string
    {
        $first = preg_split('/(?<=[.!?])\s/u', $this->text($html), 2)[0] ?? '';
        if (!preg_match_all('/\bE\s?(\d{3,4})([a-z]?)\b/u', $first, $m)) {
            return '';
        }
        $codes = array_unique(array_map(static fn ($d, $l) => $d . $l, $m[1], $m[2]));
        if (\count($codes) === 1) {
            return 'E' . reset($codes);
        }
        $roots = array_unique($m[1]);

        return \count($roots) === 1 ? 'E' . reset($roots) : '';
    }

    /** Vrai si les deux libellés partagent un mot significatif (accents, pluriels et mots vides ignorés). */
    private function sharesWord(string $a, string $b): bool
    {
        $words = static function (string $s): array {
            preg_match_all('/[a-z]{3,}/', u($s)->ascii()->lower()->toString(), $m);

            return array_map(static fn ($w) => rtrim($w, 'sx'), array_diff($m[0], ['des', 'les', 'pour', 'aux']));
        };

        return (bool) array_intersect($words($a), $words($b));
    }
}
