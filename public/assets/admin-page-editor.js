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

function buildCtaSnippet({ label, url, style, targetBlank }) {
    const safeLabel = label.trim().replaceAll('"', '&quot;');
    const safeUrl = url.trim().replaceAll('"', '&quot;');
    const safeStyle = style === 'secondary' ? 'secondary' : 'primary';
    const target = targetBlank ? ' target="blank"' : '';

    return `\n[cta label="${safeLabel}" url="${safeUrl}" style="${safeStyle}"${target}]\n`;
}

function createCtaPanel(editor) {
    const panel = document.createElement('div');
    panel.className = 'page-editor-cta-panel';
    panel.hidden = true;

    panel.innerHTML = `
        <div class="page-editor-cta-grid">
            <label class="page-editor-cta-field">
                <span>Libellé</span>
                <input type="text" data-cta-input="label" placeholder="Découvrir">
            </label>
            <label class="page-editor-cta-field">
                <span>Lien</span>
                <input type="text" data-cta-input="url" placeholder="/mon-lien ou https://...">
            </label>
            <label class="page-editor-cta-field">
                <span>Style</span>
                <select data-cta-input="style">
                    <option value="primary">Principal</option>
                    <option value="secondary">Secondaire</option>
                </select>
            </label>
            <label class="page-editor-cta-check">
                <input type="checkbox" data-cta-input="target">
                <span>Nouvel onglet</span>
            </label>
        </div>
        <div class="page-editor-cta-actions">
            <button type="button" class="page-editor-shortcut" data-cta-action="insert">Insérer le CTA</button>
            <button type="button" class="page-editor-shortcut" data-cta-action="cancel">Fermer</button>
        </div>
    `;

    const labelInput = panel.querySelector('[data-cta-input="label"]');
    const urlInput = panel.querySelector('[data-cta-input="url"]');
    const styleInput = panel.querySelector('[data-cta-input="style"]');
    const targetInput = panel.querySelector('[data-cta-input="target"]');
    const insertButton = panel.querySelector('[data-cta-action="insert"]');
    const cancelButton = panel.querySelector('[data-cta-action="cancel"]');

    function closePanel() {
        panel.hidden = true;
    }

    function openPanel() {
        panel.hidden = false;
        if (labelInput instanceof HTMLInputElement) {
            labelInput.focus();
        }
    }

    if (insertButton instanceof HTMLButtonElement) {
        insertButton.addEventListener('click', () => {
            if (!(labelInput instanceof HTMLInputElement) || !(urlInput instanceof HTMLInputElement)) {
                return;
            }

            const label = labelInput.value.trim();
            const url = urlInput.value.trim();
            if (!label || !url) {
                return;
            }

            const style = styleInput instanceof HTMLSelectElement ? styleInput.value : 'primary';
            const targetBlank = targetInput instanceof HTMLInputElement ? targetInput.checked : false;

            editor.editor.insertString(buildCtaSnippet({ label, url, style, targetBlank }));
            closePanel();
            editor.focus();
        });
    }

    if (cancelButton instanceof HTMLButtonElement) {
        cancelButton.addEventListener('click', () => {
            closePanel();
            editor.focus();
        });
    }

    return { panel, openPanel, closePanel };
}

function escapeHtml(value) {
    return value
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');
}

function renderEditorPreviewContent(content) {
    if (!content.trim()) {
        return '<p>Le contenu du bloc apparaitra ici.</p>';
    }

    let rendered = content;

    rendered = rendered.replace(/<(div|p)>\s*\[separator\]\s*<\/\1>/gi, '<hr class="content-separator">');
    rendered = rendered.replaceAll('[separator]', '<hr class="content-separator">');

    rendered = rendered.replace(/\[quote\](.*?)\[\/quote\]/gis, (_match, quote) => {
        const safeQuote = escapeHtml(quote.trim());
        if (!safeQuote) {
            return '';
        }

        return `<blockquote class="content-quote"><p>${safeQuote}</p></blockquote>`;
    });

    rendered = rendered.replace(/\[cta\s+([^\]]+)\]/gi, (_match, rawAttributes) => {
        const attributes = Object.fromEntries(
            Array.from(rawAttributes.matchAll(/(\w+)="([^"]*)"/g), (attributeMatch) => [attributeMatch[1], attributeMatch[2]])
        );

        const label = (attributes.label || '').trim();
        const url = (attributes.url || '').trim();
        if (!label || !url) {
            return '';
        }

        const style = attributes.style === 'secondary' ? 'secondary' : 'primary';
        const isBlank = attributes.target === 'blank';
        const targetAttributes = isBlank ? ' target="_blank" rel="noreferrer noopener"' : '';

        return `<p class="content-cta-wrap"><a class="button ${style} content-cta" href="${escapeHtml(url)}"${targetAttributes}>${escapeHtml(label)}</a></p>`;
    });

    if (rendered === rendered.replace(/<[^>]+>/g, '')) {
        return `<p>${escapeHtml(rendered)}</p>`;
    }

    return rendered;
}

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

    const textTools = toolbar.querySelector('.trix-button-group--text-tools');

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

    const ctaPanel = createCtaPanel(editor);

    if (textTools instanceof HTMLElement) {
        const ctaButton = document.createElement('button');
        ctaButton.type = 'button';
        ctaButton.className = 'trix-button trix-button--cta';
        ctaButton.setAttribute('title', 'Insérer un bouton CTA');
        ctaButton.setAttribute('tabindex', '-1');
        ctaButton.textContent = 'CTA';
        ctaButton.addEventListener('click', () => ctaPanel.openPanel());
        textTools.appendChild(ctaButton);
    }

    const fileButton = toolbar.querySelector('.trix-button--icon-attach');
    if (fileButton instanceof HTMLElement) {
        fileButton.remove();
    }

    const helperBar = document.createElement('div');
    helperBar.className = 'page-editor-shortcuts';

    const shortcuts = [
        { label: 'Citation', action: () => editor.editor.insertString('\n[quote]Votre citation[/quote]\n') },
        { label: 'Séparateur', action: () => editor.editor.insertString('\n[separator]\n') },
        { label: 'CTA guidé', action: () => ctaPanel.openPanel() },
    ];

    shortcuts.forEach(({ label, action }) => {
        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'page-editor-shortcut';
        button.textContent = label;
        button.addEventListener('click', () => {
            action();
            editor.focus();
        });
        helperBar.appendChild(button);
    });

    toolbar.insertAdjacentElement('afterend', helperBar);
    helperBar.insertAdjacentElement('afterend', ctaPanel.panel);
});

function initHomeSectionPreview() {
    const form = document.querySelector('form');
    if (!(form instanceof HTMLFormElement) || form.dataset.homeSectionPreviewReady === '1') {
        return;
    }

    const sectionKeyInput = form.querySelector('[name$="[sectionKey]"]');
    const titleInput = form.querySelector('[name$="[title]"]');
    const contentInput = form.querySelector('[name$="[content]"]');
    if (!(sectionKeyInput instanceof HTMLInputElement || sectionKeyInput instanceof HTMLSelectElement) || !(titleInput instanceof HTMLInputElement) || !(contentInput instanceof HTMLInputElement || contentInput instanceof HTMLTextAreaElement)) {
        return;
    }

    const titleTagInput = form.querySelector('[name$="[titleTag]"]');
    const textAlignmentInput = form.querySelector('[name$="[textAlignment]"]');
    const layoutWidthInput = form.querySelector('[name$="[layoutWidth]"]');
    const appearanceInput = form.querySelector('[name$="[appearance]"]');
    const accentToneInput = form.querySelector('[name$="[accentTone]"]');
    const showImageInput = form.querySelector('[name$="[showImage]"]');
    const imagePositionInput = form.querySelector('[name$="[imagePosition]"]');

    const preview = document.createElement('section');
    preview.className = 'home-section-admin-preview';
    preview.innerHTML = `
        <div class="home-section-admin-preview__header">
            <strong>Prévisualisation du bloc</strong>
            <span>Mise à jour en direct</span>
        </div>
        <article class="home-section-admin-preview__card">
            <div class="home-section-admin-preview__accent"></div>
            <div class="home-section-admin-preview__inner">
                <div class="home-section-admin-preview__media">Image</div>
                <div class="home-section-admin-preview__content">
                    <div class="home-section-admin-preview__section-key"></div>
                    <div class="home-section-admin-preview__title"></div>
                    <div class="home-section-admin-preview__body"></div>
                </div>
            </div>
        </article>
    `;

    const targetField = contentInput.closest('.field-text_editor, .form-group, .form-widget');
    if (targetField instanceof HTMLElement) {
        targetField.insertAdjacentElement('afterend', preview);
    } else {
        form.appendChild(preview);
    }

    const keyNode = preview.querySelector('.home-section-admin-preview__section-key');
    const titleNode = preview.querySelector('.home-section-admin-preview__title');
    const bodyNode = preview.querySelector('.home-section-admin-preview__body');
    const cardNode = preview.querySelector('.home-section-admin-preview__card');
    const innerNode = preview.querySelector('.home-section-admin-preview__inner');
    const mediaNode = preview.querySelector('.home-section-admin-preview__media');
    const contentNode = preview.querySelector('.home-section-admin-preview__content');
    const accentNode = preview.querySelector('.home-section-admin-preview__accent');

    form.dataset.homeSectionPreviewReady = '1';

    function getValue(input, fallback = '') {
        if (input instanceof HTMLInputElement || input instanceof HTMLTextAreaElement || input instanceof HTMLSelectElement) {
            return input.value || fallback;
        }

        return fallback;
    }

    function getChecked(input, fallback = true) {
        if (input instanceof HTMLInputElement && input.type === 'checkbox') {
            return input.checked;
        }

        return fallback;
    }

    function renderPreview() {
        const title = titleInput.value.trim() || 'Titre du bloc';
        const configuredTitleTag = getValue(titleTagInput, 'h2');
        const titleTag = ['h1', 'h2', 'h3'].includes(configuredTitleTag) ? configuredTitleTag : 'h2';
        const content = getValue(contentInput, '').trim();
        const alignment = getValue(textAlignmentInput, 'left');
        const width = getValue(layoutWidthInput, 'wide');
        const appearance = getValue(appearanceInput, 'default');
        const tone = getValue(accentToneInput, 'green');
        const imagePosition = getValue(imagePositionInput, 'start');
        const showImage = getChecked(showImageInput, true);
        const sectionKey = getValue(sectionKeyInput, '');

        if (keyNode instanceof HTMLElement) {
            keyNode.textContent = sectionKey || 'bloc';
        }

        if (titleNode instanceof HTMLElement) {
            titleNode.innerHTML = `<${titleTag}>${escapeHtml(title)}</${titleTag}>`;
        }

        if (bodyNode instanceof HTMLElement) {
            bodyNode.innerHTML = renderEditorPreviewContent(content);
        }

        if (cardNode instanceof HTMLElement) {
            cardNode.dataset.width = width;
            cardNode.dataset.appearance = appearance;
            cardNode.dataset.tone = tone;
        }

        if (innerNode instanceof HTMLElement) {
            innerNode.dataset.imagePosition = imagePosition;
        }

        if (contentNode instanceof HTMLElement) {
            contentNode.dataset.align = alignment;
        }

        if (mediaNode instanceof HTMLElement) {
            mediaNode.hidden = !showImage;
        }

        if (accentNode instanceof HTMLElement) {
            accentNode.dataset.tone = tone;
        }
    }

    form.addEventListener('input', renderPreview);
    form.addEventListener('change', renderPreview);
    document.addEventListener('trix-change', renderPreview);
    renderPreview();
}

document.addEventListener('DOMContentLoaded', initHomeSectionPreview);

