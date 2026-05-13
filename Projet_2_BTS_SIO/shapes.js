//  Définitions et gestion des formes SVG

// Catalogue de toutes les formes disponibles : dimensions par défaut,
// présence d'un remplissage, et fonction de génération du markup SVG interne.
const SHAPE_DEFS = {
    rectangle:     { w: 150, h: 100, hasFill: true,
        svg: (f,s,sw) => `<rect x="${sw/2}" y="${sw/2}" width="calc(100% - ${sw}px)" height="calc(100% - ${sw}px)" rx="2" fill="${f}" stroke="${s}" stroke-width="${sw}"/>`,
        viewBox: (w,h) => `0 0 ${w} ${h}`,
        shapes: (f,s,sw,w,h) => `<rect x="${sw/2}" y="${sw/2}" width="${w-sw}" height="${h-sw}" rx="2" fill="${f}" stroke="${s}" stroke-width="${sw}"/>` },
    square:        { w: 100, h: 100, hasFill: true,
        shapes: (f,s,sw,w,h) => `<rect x="${sw/2}" y="${sw/2}" width="${w-sw}" height="${h-sw}" rx="2" fill="${f}" stroke="${s}" stroke-width="${sw}"/>` },
    circle:        { w: 100, h: 100, hasFill: true,
        shapes: (f,s,sw,w,h) => { const cx=w/2,cy=h/2,r=Math.min(w,h)/2-sw/2; return `<ellipse cx="${cx}" cy="${cy}" rx="${r}" ry="${r}" fill="${f}" stroke="${s}" stroke-width="${sw}"/>`; } },
    ellipse:       { w: 160, h: 100, hasFill: true,
        shapes: (f,s,sw,w,h) => `<ellipse cx="${w/2}" cy="${h/2}" rx="${w/2-sw/2}" ry="${h/2-sw/2}" fill="${f}" stroke="${s}" stroke-width="${sw}"/>` },
    triangle:      { w: 100, h: 100, hasFill: true,
        shapes: (f,s,sw,w,h) => `<polygon points="${w/2},${sw} ${w-sw},${h-sw} ${sw},${h-sw}" fill="${f}" stroke="${s}" stroke-width="${sw}"/>` },
    diamond:       { w: 100, h: 100, hasFill: true,
        shapes: (f,s,sw,w,h) => `<polygon points="${w/2},${sw} ${w-sw},${h/2} ${w/2},${h-sw} ${sw},${h/2}" fill="${f}" stroke="${s}" stroke-width="${sw}"/>` },
    pentagon:      { w: 100, h: 100, hasFill: true,
        shapes: (f,s,sw,w,h) => {
            const cx=w/2,cy=h/2,r=Math.min(w,h)/2-sw/2;
            const pts=[];for(let i=0;i<5;i++){const a=-Math.PI/2+i*2*Math.PI/5;pts.push(`${cx+r*Math.cos(a)},${cy+r*Math.sin(a)}`);}
            return `<polygon points="${pts.join(' ')}" fill="${f}" stroke="${s}" stroke-width="${sw}"/>`;} },
    star:          { w: 100, h: 100, hasFill: true,
        shapes: (f,s,sw,w,h) => {
            const cx=w/2,cy=h/2,R=Math.min(w,h)/2-sw/2,r2=R*0.42,pts=[];
            for(let i=0;i<10;i++){const a=-Math.PI/2+i*Math.PI/5,r=i%2===0?R:r2;pts.push(`${cx+r*Math.cos(a)},${cy+r*Math.sin(a)}`);}
            return `<polygon points="${pts.join(' ')}" fill="${f}" stroke="${s}" stroke-width="${sw}"/>`;} },
    arrow_full:    { w: 140, h:  80, hasFill: true,
        shapes: (f,s,sw,w,h) => {
            const m=sw/2,hw=w*0.65,hy1=h*0.3,hy2=h*0.7;
            return `<polygon points="${m},${hy1} ${hw},${hy1} ${hw},${m} ${w-m},${h/2} ${hw},${h-m} ${hw},${hy2} ${m},${hy2}" fill="${f}" stroke="${s}" stroke-width="${sw}"/>`; } },
    arrow_outline: { w: 140, h:  60, hasFill: false,
        shapes: (f,s,sw,w,h) => {
            const y=h/2,tip=w-sw,hh=h/2-sw;
            return `<line x1="${sw}" y1="${y}" x2="${tip*0.7}" y2="${y}" stroke="${s}" stroke-width="${sw}" stroke-linecap="round"/>
                    <polyline points="${tip*0.7},${sw+hh*0.1} ${tip},${y} ${tip*0.7},${h-sw-hh*0.1}" fill="none" stroke="${s}" stroke-width="${sw}" stroke-linejoin="round" stroke-linecap="round"/>`;} },
    line:          { w: 140, h:  20, hasFill: false,
        shapes: (f,s,sw,w,h) => `<line x1="${sw}" y1="${h/2}" x2="${w-sw}" y2="${h/2}" stroke="${s}" stroke-width="${sw}" stroke-linecap="round"/>` },
};

// Génère la balise SVG complète pour un type de forme donné avec ses couleurs et dimensions.
function buildSVG(type, fill, stroke, strokeW, w, h) {
    const def = SHAPE_DEFS[type];
    if (!def) return '';
    const inner = def.shapes(fill, stroke, strokeW, w, h);
    return `<svg viewBox="0 0 ${w} ${h}" xmlns="http://www.w3.org/2000/svg" width="100%" height="100%" preserveAspectRatio="none">${inner}</svg>`;
}

// Crée et ajoute une forme SVG du type choisi avec ses couleurs par défaut, puis ferme le popup.
function addShape(type) {
    const def = SHAPE_DEFS[type];
    if (!def) return;
    const fill    = def.hasFill ? '#e7f3ff' : 'none';
    const stroke  = '#0066cc';
    const strokeW = 2;
    const w = def.w, h = def.h;

    const el = document.createElement('div');
    el.className       = 'element';
    el.dataset.type    = 'shape';
    el.dataset.shape   = type;
    el.dataset.fill    = fill;
    el.dataset.stroke  = stroke;
    el.dataset.strokeW = strokeW;
    el.style.width     = w + 'px';
    el.style.height    = h + 'px';
    el.style.border    = 'none';
    el.innerHTML       = buildSVG(type, fill, stroke, strokeW, w, h);

    addElementToCanvas(el);
    closeShapePopup();
}

// Reconstruit le SVG de la forme sélectionnée pour refléter les changements de couleur ou de taille.
function refreshSelectedShape() {
    if (!selectedElement || selectedElement.dataset.type !== 'shape') return;
    const w  = parseInt(selectedElement.style.width)  || 150;
    const h  = parseInt(selectedElement.style.height) || 100;
    const sw = parseFloat(selectedElement.dataset.strokeW) || 2;
    const fill   = selectedElement.dataset.fill;
    const stroke = selectedElement.dataset.stroke;
    const type   = selectedElement.dataset.shape;
    const closeBtn = selectedElement.querySelector('.element-close');
    const handles  = [...selectedElement.querySelectorAll('.resize-handle')];
    selectedElement.innerHTML = buildSVG(type, fill, stroke, sw, w, h);
    if (closeBtn) selectedElement.appendChild(closeBtn);
    handles.forEach(h => selectedElement.appendChild(h));
}

// Redimensionne la forme sélectionnée sur l'axe donné et rafraîchit son SVG.
function onShapeSizeChange(axis, val) {
    let v = Math.min(600, Math.max(20, parseInt(val) || 20));
    if (axis === 'w') { shapeWRange.value = v; shapeWNum.value = v; if (selectedElement) selectedElement.style.width  = v + 'px'; }
    else              { shapeHRange.value = v; shapeHNum.value = v; if (selectedElement) selectedElement.style.height = v + 'px'; }
    refreshSelectedShape();
}

// Applique les nouvelles couleurs de remplissage et de bordure à la forme sélectionnée.
function onShapeColorChange() {
    if (!selectedElement || selectedElement.dataset.type !== 'shape') return;
    const def = SHAPE_DEFS[selectedElement.dataset.shape];
    if (def && def.hasFill && !noFillChk.checked) {
        const f = shapeFillColor.value;
        selectedElement.dataset.fill = f;
        shapeFillHex.value = f;
    }
    const s = shapeStrokeColor.value;
    selectedElement.dataset.stroke = s;
    shapeStrokeHex.value = s;
    refreshSelectedShape();
}

// Valide et applique une couleur saisie manuellement en hexadécimal pour le fill ou le stroke.
function onShapeHexChange(which, val) {
    if (!/^#[0-9a-fA-F]{6}$/.test(val)) return;
    if (which === 'fill') {
        shapeFillColor.value = val;
        if (selectedElement) selectedElement.dataset.fill = val;
    } else {
        shapeStrokeColor.value = val;
        if (selectedElement) selectedElement.dataset.stroke = val;
    }
    refreshSelectedShape();
}

// Active ou désactive le remplissage transparent de la forme selon la case à cocher.
function onNoFillChange() {
    if (!selectedElement || selectedElement.dataset.type !== 'shape') return;
    if (noFillChk.checked) {
        selectedElement.dataset.fill = 'none';
        shapeFillColor.disabled = true;
        shapeFillHex.disabled   = true;
    } else {
        const c = shapeFillColor.value;
        selectedElement.dataset.fill = c;
        shapeFillColor.disabled = false;
        shapeFillHex.disabled   = false;
    }
    refreshSelectedShape();
}

// Modifie l'épaisseur de la bordure de la forme sélectionnée et rafraîchit son SVG.
function onStrokeWidthChange(val) {
    let sw = Math.min(20, Math.max(0, parseInt(val) || 0));
    strokeWRange.value = sw; strokeWNum.value = sw;
    if (selectedElement) selectedElement.dataset.strokeW = sw;
    refreshSelectedShape();
}

// Ouvre le popup de sélection de forme.
function openShapePopup()  { document.getElementById('shapeOverlay').classList.add('open'); }
// Ferme le popup de sélection de forme.
function closeShapePopup() { document.getElementById('shapeOverlay').classList.remove('open'); }
// Ferme le popup uniquement si le clic est sur l'overlay (fond sombre).
function closeShapePopupOnOverlay(e) { if (e.target.id === 'shapeOverlay') closeShapePopup(); }
