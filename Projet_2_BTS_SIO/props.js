//  Panneau de propriétés (mise à jour selon l'élément sélectionné)

// Affiche le panneau de propriétés adapté au type de l'élément sélectionné (texte, forme ou image).
function updatePropsPanel(el) {
    textProps.classList.remove('visible');
    shapeProps.classList.remove('visible');
    imageProps.classList.remove('visible');
    if (!el) return;

    if (el.dataset.type === 'text') {
        const ta   = getTextarea(el);
        const size = ta ? parseInt(ta.style.fontSize) || 14 : 14;
        fontSizeRange.value = size;
        fontSizeNum.value   = size;
        const bg = el.style.background || 'transparent';
        const isTransparent = bg === 'transparent' || bg === '';
        textTransparent.checked = isTransparent;
        textBgColorRow.style.display = isTransparent ? 'none' : '';
        if (!isTransparent) {
            textBgColor.value = bg;
            document.getElementById('textBgHex').value = bg;
        }
        textProps.classList.add('visible');

    } else if (el.dataset.type === 'shape') {
        const w  = parseInt(el.style.width)  || 150;
        const h  = parseInt(el.style.height) || 100;
        const sw = parseFloat(el.dataset.strokeW) || 2;
        const fill   = el.dataset.fill   || '#e7f3ff';
        const stroke = el.dataset.stroke || '#0066cc';
        const def = SHAPE_DEFS[el.dataset.shape];

        shapeWRange.value = w; shapeWNum.value = w;
        shapeHRange.value = h; shapeHNum.value = h;
        strokeWRange.value = sw; strokeWNum.value = sw;

        const hasFill = def ? def.hasFill : true;
        fillColorRow.style.display = hasFill ? '' : 'none';

        if (hasFill) {
            const isNone = fill === 'none';
            noFillChk.checked = isNone;
            const displayFill = isNone ? '#ffffff' : fill;
            shapeFillColor.value = displayFill;
            shapeFillHex.value   = displayFill;
            shapeFillColor.disabled = isNone;
            shapeFillHex.disabled   = isNone;
        }

        shapeStrokeColor.value = stroke;
        shapeStrokeHex.value   = stroke;
        shapeProps.classList.add('visible');

    } else {
        const w  = parseInt(el.style.width)  || 200;
        const h  = parseInt(el.style.height) || 150;
        const op = Math.round((parseFloat(el.style.opacity) || 1) * 100);
        const clip = el.dataset.clip || 'none';
        document.getElementById('imgWRange').value = w;
        document.getElementById('imgWNum').value   = w;
        document.getElementById('imgHRange').value = h;
        document.getElementById('imgHNum').value   = h;
        document.getElementById('imgOpacityRange').value = op;
        document.getElementById('imgOpacityNum').value   = op;
        document.querySelectorAll('.shape-clip-btn').forEach(b => {
            b.classList.toggle('active', b.dataset.clip === clip);
        });
        imageProps.classList.add('visible');
    }
}
