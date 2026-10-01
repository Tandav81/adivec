/**
 * Personnalisation de l'éditeur Trix (EasyAdmin) pour les news Adivec.
 *
 * - Ajoute un bouton « Mettre en avant » (balise <mark>) dans la barre d'outils.
 *   L'attribut `highlight` lui-même est déclaré côté PHP via setTrixEditorConfig()
 *   dans BlogPostCrudController (il doit exister AVANT l'initialisation de l'éditeur).
 * - Libellés français sur les boutons de titre.
 *
 * Chargé via configureAssets() dans BlogPostCrudController.
 */
(function () {
    'use strict';

    function addHighlightButton(editorElement) {
        var toolbar = editorElement.toolbarElement;
        if (!toolbar || toolbar.querySelector('[data-trix-attribute="highlight"]')) {
            return;
        }

        var group = toolbar.querySelector('[data-trix-button-group="text-tools"]');
        if (!group) {
            return;
        }

        var button = document.createElement('button');
        button.type = 'button';
        button.className = 'trix-button trix-button--icon trix-button--icon-highlight';
        button.setAttribute('data-trix-attribute', 'highlight');
        button.setAttribute('title', 'Mettre en avant');
        button.setAttribute('tabindex', '-1');
        button.textContent = 'Mettre en avant';
        group.appendChild(button);

        var heading = toolbar.querySelector('[data-trix-attribute="heading1"]');
        if (heading) {
            heading.setAttribute('title', 'Titre de section');
        }
    }

    // Éditeurs initialisés après le chargement de ce script
    document.addEventListener('trix-initialize', function (event) {
        addHighlightButton(event.target);
    });

    // Éditeurs éventuellement déjà initialisés
    document.querySelectorAll('trix-editor').forEach(function (el) {
        if (el.toolbarElement) {
            addHighlightButton(el);
        }
    });
})();
