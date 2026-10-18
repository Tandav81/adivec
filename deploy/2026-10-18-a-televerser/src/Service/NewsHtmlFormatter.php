<?php

namespace App\Service;

/**
 * Restructure le HTML des actualités saisi dans l'éditeur Trix du back-office.
 *
 * Trix produit un seul <div> par bloc, découpé avec des <br> : pas de vrais
 * paragraphes ni de vrais intertitres. Les rédacteurs simulent :
 *   - les intertitres par une ligne entièrement en gras suivie d'un saut de ligne ;
 *   - les listes par des lignes qui commencent par ✔ / ✔️ / ✅.
 *
 * Ce formateur en déduit du HTML sémantique :
 *   <br><br>                    → nouveau <p>
 *   ligne seule en <strong>     → <h2> (stylé par .news-content h2)
 *   ligne seule en gras précédée d'un emoji (🥣 Soupes) → <h3>
 *   lignes ✔ / ✅ consécutives  → <ul class="news-checklist">, lignes « • » → <ul>
 *
 * Il ne touche pas aux autres blocs (h1, ul, blockquote… déjà sémantiques)
 * et ne modifie pas le contenu en base : l'admin reste inchangé.
 */
final class NewsHtmlFormatter
{
    /** Longueur maximale d'une ligne en gras pour être considérée comme un intertitre. */
    private const HEADING_MAX = 120;

    private const CHECK = '(?:✔|✅|☑)\x{FE0F}?';
    private const BULLET = '(?:•|·)';
    /** Emoji en tête d'intertitre (🥣, 🌱, 💰…) */
    private const EMOJI = '(?:\p{Extended_Pictographic}\x{FE0F}?\s*)';

    public function format(?string $html): string
    {
        $html = (string) $html;
        if ('' === trim($html)) {
            return '';
        }

        $html = preg_replace('#<br\s*/?>#i', '<br>', $html);
        // Un <br> ne doit jamais être à l'intérieur d'un <strong>/<em> (sinon le découpage
        // en lignes casse les balises) : « <strong>a<br>b</strong> » → « <strong>a</strong><br><strong>b</strong> »
        do {
            $html = preg_replace('#<(strong|em)>((?:(?!</?\1>).)*?)<br>#s', '<$1>$2</$1><br><$1>', $html, -1, $n);
        } while ($n > 0);
        $html = preg_replace('#<(strong|em)>\s*</\1>#', '', $html);

        // Blocs Trix : <div> sans <div> imbriqué
        $html = preg_replace_callback(
            '#<div>((?:(?!<div\b).)*?)</div>#s',
            fn (array $m): string => $this->block($m[1]),
            $html
        );

        // Deux listes séparées par une ligne vide → une seule liste
        return preg_replace('#</ul>(<ul(?: class="[^"]*")?>)#', '', $html);
    }

    private function block(string $inner): string
    {
        $out = '';
        foreach (preg_split('#(?:<br>\s*){2,}#', trim($inner)) as $para) {
            $lines = array_values(array_filter(
                array_map('trim', preg_split('#<br>#', trim($para))),
                fn (string $l): bool => '' !== trim(strip_tags($l, '<img>'))
            ));
            if (!$lines) {
                continue;
            }

            if ($heading = $this->heading($lines[0], count($lines) > 1)) {
                [$tag, $text] = $heading;
                $out .= "<$tag>$text</$tag>";
                array_shift($lines);
            }

            $text = [];
            $items = [];
            $listClass = '';
            foreach ($lines as $line) {
                foreach (['news-checklist' => self::CHECK, '' => self::BULLET] as $class => $marker) {
                    if (preg_match('#^(?:<strong>)?\s*' . $marker . '#u', $line)) {
                        if ($text) {
                            $out .= '<p>' . implode('<br>', $text) . '</p>';
                            $text = [];
                        }
                        $listClass = $items ? $listClass : $class;
                        $items[] = $this->stripMarker($line, $marker);
                        continue 2;
                    }
                }
                if ($items) {
                    $out .= $this->list($items, $listClass);
                    $items = [];
                }
                $text[] = $line;
            }
            if ($items) {
                $out .= $this->list($items, $listClass);
            }
            if ($text) {
                $out .= '<p>' . implode('<br>', $text) . '</p>';
            }
        }

        return $out;
    }

    /**
     * [balise, texte] si la ligne n'est qu'un <strong> (éventuellement précédé d'un emoji), sinon null.
     *
     * @return array{0: string, 1: string}|null
     */
    private function heading(string $line, bool $followedByText): ?array
    {
        $re = '#^(' . self::EMOJI . '?)<strong>(' . self::EMOJI . '?)([^<]+)</strong>\s*:?$#u';
        if (!preg_match($re, $line, $m)) {
            return null;
        }
        $emoji = trim($m[1] . $m[2]);
        $text = trim($m[3]);
        // ✅ est aussi un emoji : une ligne « ✅ … » est un élément de liste, pas un intertitre
        if (preg_match('#^(?:' . self::CHECK . '|' . self::BULLET . ')#u', $emoji . $text) || mb_strlen($text) > self::HEADING_MAX) {
            return null;
        }
        // Phrase en gras isolée (se termine par un point) : mise en avant, pas un titre
        if (!$followedByText && str_ends_with($text, '.')) {
            return null;
        }
        $text = rtrim($text, ' :');

        return '' === $emoji ? ['h2', $text] : ['h3', $emoji . ' ' . $text];
    }

    private function stripMarker(string $line, string $marker): string
    {
        $line = preg_replace('#^(<strong>)?\s*' . $marker . '\s*#u', '$1', $line);

        // « <strong></strong> texte » ou « </strong> texte » laissés par le retrait de l'icône
        return trim(preg_replace('#^(?:<strong>\s*</strong>|</strong>)\s*#', '', $line));
    }

    private function list(array $items, string $class): string
    {
        $attr = '' === $class ? '' : ' class="' . $class . '"';

        return "<ul$attr><li>" . implode('</li><li>', $items) . '</li></ul>';
    }
}
