document.addEventListener('trix-before-initialize', () => {
    if (!window.Trix || !window.Trix.config) {
        return;
    }

    window.Trix.config.blockAttributes.heading1 = {
        tagName: 'h2',
        terminal: true,
        breakOnReturn: true,
        group: false,
    };

    window.Trix.config.blockAttributes.heading2 = {
        tagName: 'h3',
        terminal: true,
        breakOnReturn: true,
        group: false,
    };
});

document.addEventListener('trix-initialize', (event) => {
    const editor = event.target;
    if (!(editor instanceof HTMLElement) || editor.dataset.pageEditorReady === '1') {
        return;
    }

    const inputId = editor.getAttribute('input');
    if (!inputId) {
        return;
    }

    const source = document.getElementById(inputId);
    if (!(source instanceof HTMLElement) || source.dataset.pageRichEditor !== '1') {
        return;
    }

    editor.dataset.pageEditorReady = '1';

    const toolbar = editor.toolbarElement;
    if (!(toolbar instanceof HTMLElement)) {
        return;
    }

    const blockTools = toolbar.querySelector('.trix-button-group--block-tools');
    if (!(blockTools instanceof HTMLElement)) {
        return;
    }

    const headingTwoButton = document.createElement('button');
    headingTwoButton.type = 'button';
    headingTwoButton.className = 'trix-button';
    headingTwoButton.setAttribute('data-trix-attribute', 'heading1');
    headingTwoButton.setAttribute('title', 'Titre de niveau 2');
    headingTwoButton.setAttribute('tabindex', '-1');
    headingTwoButton.textContent = 'H2';

    const headingThreeButton = document.createElement('button');
    headingThreeButton.type = 'button';
    headingThreeButton.className = 'trix-button';
    headingThreeButton.setAttribute('data-trix-attribute', 'heading2');
    headingThreeButton.setAttribute('title', 'Titre de niveau 3');
    headingThreeButton.setAttribute('tabindex', '-1');
    headingThreeButton.textContent = 'H3';

    blockTools.prepend(headingThreeButton);
    blockTools.prepend(headingTwoButton);

    const fileButton = toolbar.querySelector('.trix-button--icon-attach');
    if (fileButton instanceof HTMLElement) {
        fileButton.remove();
    }

    const helperBar = document.createElement('div');
    helperBar.className = 'page-editor-shortcuts';

    const shortcuts = [
        { label: 'Citation', snippet: '\n[quote]Votre citation[/quote]\n' },
        { label: 'Séparateur', snippet: '\n[separator]\n' },
        { label: 'Bouton lien', snippet: '\n[cta label=\"Découvrir\" url=\"https://\"]\n' },
    ];

    shortcuts.forEach(({ label, snippet }) => {
        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'page-editor-shortcut';
        button.textContent = label;
        button.addEventListener('click', () => {
            editor.editor.insertString(snippet);
            editor.focus();
        });
        helperBar.appendChild(button);
    });

    toolbar.insertAdjacentElement('afterend', helperBar);
});
