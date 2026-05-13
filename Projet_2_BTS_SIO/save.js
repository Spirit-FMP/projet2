//  Sauvegarde du projet et export PDF

// Sérialise tous les éléments du canvas en tableau JSON (type, position, taille, contenu).
function serializeCanvas() {
    const elements = [];
    canvas.querySelectorAll('.element').forEach(el => {
        const type = el.dataset.type;
        const base = {
            type,
            left:   el.style.left,
            top:    el.style.top,
            width:  el.style.width,
            height: el.style.height,
        };
        if (type === 'text') {
            const ta = el.querySelector('textarea');
            base.text       = ta ? ta.value : '';
            base.fontSize   = ta ? ta.style.fontSize : '14px';
            base.background = el.style.background || 'transparent';
        } else if (type === 'shape') {
            base.shape   = el.dataset.shape;
            base.fill    = el.dataset.fill;
            base.stroke  = el.dataset.stroke;
            base.strokeW = el.dataset.strokeW;
        } else {
            const img = el.querySelector('img');
            base.src     = img ? img.src : '';
            base.clip    = el.dataset.clip || 'none';
            base.opacity = el.style.opacity || '1';
        }
        elements.push(base);
    });
    return elements;
}

// Reconstruit tous les éléments du canvas à partir des données JSON sauvegardées.
function deserializeCanvas(elements) {
    canvas.innerHTML = '';
    selectedElement = null;
    updatePropsPanel(null);
    elements.forEach(data => {
        if (data.type === 'text') {
            const el = document.createElement('div');
            el.className        = 'element';
            el.dataset.type     = 'text';
            el.style.width      = data.width;
            el.style.height     = data.height;
            el.style.left       = data.left;
            el.style.top        = data.top;
            el.style.padding    = '6px';
            el.style.background = data.background || 'transparent';
            el.style.border     = 'none';
            const ta = document.createElement('textarea');
            ta.value = data.text || '';
            ta.style.fontSize = data.fontSize || '14px';
            el.appendChild(ta);
            addElementToCanvas(el);
        } else if (data.type === 'shape') {
            const el = document.createElement('div');
            el.className       = 'element';
            el.dataset.type    = 'shape';
            el.dataset.shape   = data.shape;
            el.dataset.fill    = data.fill;
            el.dataset.stroke  = data.stroke;
            el.dataset.strokeW = data.strokeW;
            el.style.width  = data.width;
            el.style.height = data.height;
            el.style.left   = data.left;
            el.style.top    = data.top;
            el.style.border = 'none';
            const w = parseInt(data.width), h = parseInt(data.height);
            el.innerHTML = buildSVG(data.shape, data.fill, data.stroke, parseFloat(data.strokeW), w, h);
            addElementToCanvas(el);
        } else {
            const el = document.createElement('div');
            el.className     = 'element';
            el.dataset.type  = 'image';
            el.dataset.clip  = data.clip || 'none';
            el.style.width   = data.width;
            el.style.height  = data.height;
            el.style.left    = data.left;
            el.style.top     = data.top;
            el.style.opacity = data.opacity || '1';
            el.style.padding = '0';
            el.style.border  = 'none';
            const img = document.createElement('img');
            img.src = data.src;
            img.style.cssText = 'width:100%;height:100%;display:block;object-fit:cover;';
            if (data.clip && data.clip !== 'none') img.style.clipPath = getClipPath(data.clip);
            el.appendChild(img);
            addElementToCanvas(el);
        }
    });
}

// Charge automatiquement un projet existant si un paramètre "projet" est présent dans l'URL.
(async function checkLoadProject() {
    const params    = new URLSearchParams(window.location.search);
    const nomProjet = params.get('projet');
    if (!nomProjet) return;
    document.getElementById('saveNom').value = decodeURIComponent(nomProjet);
    try {
        const res  = await fetch(EC2_URL + '/api_get.php?nom=' + encodeURIComponent(nomProjet) + '&user_id=' + encodeURIComponent(USER_ID));
        const data = await res.json();
        if (data.success && data.elements && data.elements.length > 0) {
            deserializeCanvas(data.elements);
        }
    } catch(e) { console.warn('Impossible de charger le projet :', e); }
})();

// Ouvre le modal de sauvegarde et pré-remplit le nom si le projet est déjà chargé depuis l'URL.
function openSaveModal() {
    document.getElementById('saveStatus').textContent = '';
    const params = new URLSearchParams(window.location.search);
    if (!params.get('projet')) document.getElementById('saveNom').value = '';
    document.getElementById('saveOverlay').classList.add('open');
    setTimeout(() => document.getElementById('saveNom').focus(), 100);
}

// Ferme le modal de sauvegarde.
function closeSaveModal() {
    document.getElementById('saveOverlay').classList.remove('open');
}

// Ferme le modal de sauvegarde uniquement si le clic est sur l'overlay (fond sombre).
function closeSaveModalOnOverlay(e) {
    if (e.target.id === 'saveOverlay') closeSaveModal();
}

// Capture le canvas en image PNG, sérialise les éléments, puis envoie tout à l'API de sauvegarde.
async function doSave() {
    const nom        = document.getElementById('saveNom').value.trim();
    const statusEl   = document.getElementById('saveStatus');
    const confirmBtn = document.getElementById('btnConfirmSave');

    if (!nom) { statusEl.style.color = '#c0392b'; statusEl.textContent = 'Veuillez saisir un nom.'; return; }

    statusEl.style.color = '#555';
    statusEl.textContent = '⏳ Capture en cours…';
    confirmBtn.disabled  = true;

    const prevSel = selectedElement;
    if (selectedElement) selectedElement.classList.remove('selected');

    try {
        const pageEl    = document.getElementById('canvas');
        const canvasImg = await html2canvas(pageEl, {
            scale: 2, useCORS: true, backgroundColor: '#ffffff', logging: false,
            onclone: doc => {
                doc.querySelectorAll('.element-close, .resize-handle').forEach(el => el.style.display = 'none');
                doc.querySelectorAll('.element.behind').forEach(el => el.style.display = 'none');
            }
        });
        const imageB64 = canvasImg.toDataURL('image/png');
        const elements = serializeCanvas();

        statusEl.textContent = '📡 Envoi au serveur…';
        const res  = await fetch(EC2_URL + '/api_save.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ nom, image: imageB64, elements, user_id: USER_ID, secret: API_SECRET })
        });
        const data = await res.json();

        if (data.success) {
            statusEl.style.color = '#2e7d32';
            statusEl.textContent = '✅ Projet sauvegardé avec succès !';
            history.replaceState(null, '', '?projet=' + encodeURIComponent(nom));
            setTimeout(closeSaveModal, 1500);
        } else {
            throw new Error(data.error || 'Erreur inconnue');
        }
    } catch (err) {
        statusEl.style.color = '#c0392b';
        statusEl.textContent = 'Erreur : ' + err.message;
    } finally {
        confirmBtn.disabled = false;
        if (prevSel) prevSel.classList.add('selected');
    }
}

// Génère et télécharge un PDF A4 à partir d'une capture du canvas via html2canvas et jsPDF.
async function exportPDF() {
    const btn = document.getElementById('btnPdf');
    btn.disabled = true;
    btn.textContent = '⏳ Génération…';
    const prevSelected = selectedElement;
    if (selectedElement) selectedElement.classList.remove('selected');

    try {
        const { jsPDF } = window.jspdf;
        const pageEl = document.getElementById('canvas');
        const canvasImg = await html2canvas(pageEl, {
            scale: 2,
            useCORS: true,
            backgroundColor: '#ffffff',
            logging: false,
            onclone: function(doc) {
                doc.querySelectorAll('.element-close, .resize-handle').forEach(el => {
                    el.style.display = 'none';
                });
                doc.querySelectorAll('.element.behind').forEach(el => {
                    el.style.display = 'none';
                });
            }
        });
        const imgData = canvasImg.toDataURL('image/png');
        const pdf = new jsPDF({ orientation: 'portrait', unit: 'mm', format: 'a4' });
        pdf.addImage(imgData, 'PNG', 0, 0, 210, 297);
        pdf.save('projet.pdf');
    } catch(err) {
        alert('Erreur lors de la génération du PDF : ' + err.message);
    } finally {
        btn.disabled = false;
        btn.innerHTML = '📄 Enregistrer en PDF';
        if (prevSelected) prevSelected.classList.add('selected');
    }
}
