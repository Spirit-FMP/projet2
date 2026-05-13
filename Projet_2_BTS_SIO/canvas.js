// Sélection, déplacement et redimensionnement des éléments

// Variables d'état partagées pour le drag & drop et le resize.
let selectedElement = null;
let isDragging  = false;
let dragOffset  = { x: 0, y: 0 };
let isResizing  = false;
let resizeDir   = '';
let resizeStart = {};

// Crée les 8 poignées de redimensionnement (coins + milieux) et leur attache l'événement resize.
function createHandles() {
    return ['nw','n','ne','e','se','s','sw','w'].map(dir => {
        const h = document.createElement('div');
        h.className   = 'resize-handle ' + dir;
        h.dataset.dir = dir;
        h.addEventListener('mousedown', startResize);
        return h;
    });
}

// Ajoute un élément sur le canvas avec son bouton de suppression, ses poignées et ses listeners drag/click.
function addElementToCanvas(el) {
    if (!el.style.left) el.style.left = '50px';
    if (!el.style.top)  el.style.top  = '50px';
    const btn = document.createElement('button');
    btn.className   = 'element-close';
    btn.textContent = '✕';
    btn.addEventListener('click', deleteElement);
    el.appendChild(btn);
    createHandles().forEach(h => el.appendChild(h));
    el.addEventListener('mousedown', startDrag);
    el.addEventListener('click', selectElement);
    canvas.appendChild(el);
}

// Sélectionne l'élément cliqué et met à jour le panneau de propriétés.
function selectElement(e) {
    if (e.target.classList.contains('element-close') ||
        e.target.classList.contains('resize-handle')) return;
    if (selectedElement) selectedElement.classList.remove('selected');
    selectedElement = e.currentTarget;
    selectedElement.classList.add('selected');
    updatePropsPanel(selectedElement);
}

// Supprime l'élément du canvas et réinitialise la sélection et le panneau de propriétés.
function deleteElement(e) {
    e.stopPropagation();
    e.currentTarget.closest('.element').remove();
    selectedElement = null;
    updatePropsPanel(null);
}

// Place l'élément derrière le canvas (z-index négatif) s'il sort complètement de la zone de travail.
function updateZIndex(el, x, y, cr) {
    if (!cr) { const r = canvas.getBoundingClientRect(); cr = { width: r.width, height: r.height }; }
    const w  = el.offsetWidth  || 0;
    const h  = el.offsetHeight || 0;
    const outside = (x + w <= 0) || (y + h <= 0) || (x >= cr.width) || (y >= cr.height);
    el.classList.toggle('behind', outside);
}

// Démarre le déplacement d'un élément en mémorisant l'offset entre la souris et l'élément.
function startDrag(e) {
    if (e.target.classList.contains('element-close') ||
        e.target.classList.contains('resize-handle') ||
        e.target.tagName === 'TEXTAREA') return;
    isDragging = true;
    selectElement(e);
    const rect   = e.currentTarget.getBoundingClientRect();
    dragOffset.x = e.clientX - rect.left;
    dragOffset.y = e.clientY - rect.top;
    document.addEventListener('mousemove', drag);
    document.addEventListener('mouseup',   stopDrag);
    e.preventDefault();
}

// Déplace l'élément sélectionné en suivant la souris et met à jour son z-index si hors canvas.
function drag(e) {
    if (!isDragging || !selectedElement) return;
    const cr = canvas.getBoundingClientRect();
    let x = e.clientX - cr.left - dragOffset.x;
    let y = e.clientY - cr.top  - dragOffset.y;
    selectedElement.style.left = x + 'px';
    selectedElement.style.top  = y + 'px';
    updateZIndex(selectedElement, x, y, cr);
}

// Arrête le déplacement en supprimant les listeners mousemove et mouseup du document.
function stopDrag() {
    isDragging = false;
    document.removeEventListener('mousemove', drag);
    document.removeEventListener('mouseup',   stopDrag);
}

// Démarre le redimensionnement en capturant la direction de la poignée et l'état initial de l'élément.
function startResize(e) {
    e.stopPropagation(); e.preventDefault();
    isResizing = true;
    resizeDir  = e.target.dataset.dir;
    const el   = e.target.closest('.element');
    selectElement({ currentTarget: el, target: e.target });
    const cr = canvas.getBoundingClientRect();
    resizeStart = {
        mouseX: e.clientX, mouseY: e.clientY,
        left:   parseInt(el.style.left) || 0,
        top:    parseInt(el.style.top)  || 0,
        width:  el.offsetWidth, height: el.offsetHeight,
        cw: cr.width, ch: cr.height,
    };
    document.addEventListener('mousemove', doResize);
    document.addEventListener('mouseup',   stopResize);
}

// Redimensionne l'élément en temps réel selon la direction de la poignée et le déplacement de la souris.
function doResize(e) {
    if (!isResizing || !selectedElement) return;
    const d = resizeDir, s = resizeStart;
    const dx = e.clientX - s.mouseX, dy = e.clientY - s.mouseY;
    const MW = 20, MH = 20;
    let left = s.left, top = s.top, w = s.width, h = s.height;
    if (d.includes('e')) w = Math.max(MW, s.width + dx);
    if (d.includes('w')) { const nw=Math.max(MW,s.width-dx); left=s.left+s.width-nw; w=nw; }
    if (d.includes('s')) h = Math.max(MH, s.height + dy);
    if (d.includes('n')) { const nh=Math.max(MH,s.height-dy); top=s.top+s.height-nh; h=nh; }
    selectedElement.style.left=left+'px'; selectedElement.style.top=top+'px';
    selectedElement.style.width=w+'px';   selectedElement.style.height=h+'px';
    updateZIndex(selectedElement, left, top);
    if (selectedElement.dataset.type === 'shape') {
        shapeWRange.value=w; shapeWNum.value=w;
        shapeHRange.value=h; shapeHNum.value=h;
        refreshSelectedShape();
    } else if (selectedElement.dataset.type === 'image') {
        document.getElementById('imgWRange').value=w; document.getElementById('imgWNum').value=w;
        document.getElementById('imgHRange').value=h; document.getElementById('imgHNum').value=h;
    }
}

// Arrête le redimensionnement en supprimant les listeners mousemove et mouseup du document.
function stopResize() {
    isResizing = false;
    document.removeEventListener('mousemove', doResize);
    document.removeEventListener('mouseup',   stopResize);
}
