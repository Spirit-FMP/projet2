// Création et propriétés des zones de texte

// Retourne le textarea contenu dans un élément texte, ou null si absent.
function getTextarea(el) { return el ? el.querySelector('textarea') : null; }

// Crée et ajoute une zone de texte éditable par défaut sur le canvas.
function addTextBox() {
    const el = document.createElement('div');
    el.className        = 'element';
    el.dataset.type     = 'text';
    el.style.width      = '180px';
    el.style.height     = '80px';
    el.style.padding    = '6px';
    el.style.background = 'transparent';
    el.style.border     = 'none';
    const ta = document.createElement('textarea');
    ta.placeholder = 'Entrez du texte...';
    ta.value = 'Texte';
    el.appendChild(ta);
    addElementToCanvas(el);
}

// Applique la nouvelle taille de police au texte sélectionné et synchronise slider et champ numérique.
function onFontSizeChange(val) {
    let size = Math.min(96, Math.max(8, parseInt(val) || 8));
    fontSizeRange.value = size; fontSizeNum.value = size;
    if (selectedElement && selectedElement.dataset.type === 'text') {
        const ta = getTextarea(selectedElement);
        if (ta) ta.style.fontSize = size + 'px';
    }
}

// Bascule le fond du texte entre transparent et une couleur selon la case à cocher.
function onTextBgChange() {
    if (!selectedElement || selectedElement.dataset.type !== 'text') return;
    if (textTransparent.checked) {
        selectedElement.style.background = 'transparent';
        textBgColorRow.style.display = 'none';
    } else {
        const c = textBgColor.value;
        selectedElement.style.background = c;
        textBgColorRow.style.display = '';
    }
}

// Met à jour la couleur de fond du texte sélectionné et synchronise le champ hex.
function onTextBgColorChange(val) {
    if (!selectedElement || selectedElement.dataset.type !== 'text') return;
    selectedElement.style.background = val;
    document.getElementById('textBgHex').value = val;
}
