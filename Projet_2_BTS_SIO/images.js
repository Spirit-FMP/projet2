// Ajout, remplacement et propriétés des images

// Indique si l'ouverture du sélecteur de fichier est pour un remplacement (true) ou un ajout (false).
let pendingImageReplace = false;

// Ouvre le sélecteur de fichier pour ajouter une nouvelle image sur le canvas.
function triggerImageUpload() {
    pendingImageReplace = false;
    document.getElementById('imageFileInput').value = '';
    document.getElementById('imageFileInput').click();
}

// Ouvre le sélecteur de fichier pour remplacer l'image de l'élément actuellement sélectionné.
function triggerImageReplace() {
    pendingImageReplace = true;
    document.getElementById('imageFileInput').value = '';
    document.getElementById('imageFileInput').click();
}

// Lit le fichier image sélectionné et soit remplace l'image existante, soit en crée une nouvelle.
function onImageFileSelected(event) {
    const file = event.target.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = function(e) {
        if (pendingImageReplace && selectedElement && selectedElement.dataset.type === 'image') {
            const img = selectedElement.querySelector('img');
            if (img) img.src = e.target.result;
        } else {
            createImageElement(e.target.result);
        }
    };
    reader.readAsDataURL(file);
}

// Crée un élément image redimensionnable à partir d'une source base64 et l'ajoute au canvas.
function createImageElement(src) {
    const el = document.createElement('div');
    el.className     = 'element';
    el.dataset.type  = 'image';
    el.dataset.clip  = 'none';
    el.style.width   = '200px';
    el.style.height  = '150px';
    el.style.padding = '0';
    el.style.border  = 'none';
    const img = document.createElement('img');
    img.src           = src;
    img.style.cssText = 'width:100%;height:100%;display:block;object-fit:cover;';
    el.appendChild(img);
    addElementToCanvas(el);
}

// Retourne la valeur CSS clip-path correspondant à la forme de découpe demandée.
function getClipPath(shape) {
    switch(shape) {
        case 'circle':   return 'circle(50% at 50% 50%)';
        case 'ellipse':  return 'ellipse(50% 40% at 50% 50%)';
        case 'triangle': return 'polygon(50% 0%, 0% 100%, 100% 100%)';
        case 'diamond':  return 'polygon(50% 0%, 100% 50%, 50% 100%, 0% 50%)';
        case 'star': {
            const pts = [];
            for (let i = 0; i < 10; i++) {
                const a = -Math.PI/2 + i * Math.PI/5;
                const r = i % 2 === 0 ? 50 : 21;
                pts.push(`${50 + r*Math.cos(a)}% ${50 + r*Math.sin(a)}%`);
            }
            return `polygon(${pts.join(', ')})`;
        }
        default: return 'none';
    }
}

// Applique une forme de découpe (cercle, rectangle…) à l'image sélectionnée.
function setImgClip(shape) {
    if (!selectedElement || selectedElement.dataset.type !== 'image') return;
    selectedElement.dataset.clip = shape;
    const img = selectedElement.querySelector('img');
    if (img) {
        const cp = getClipPath(shape);
        img.style.clipPath = cp === 'none' ? '' : cp;
    }
    document.querySelectorAll('.shape-clip-btn').forEach(b => {
        b.classList.toggle('active', b.dataset.clip === shape);
    });
}

// Met à jour la largeur ou la hauteur de l'image sélectionnée via le slider ou le champ numérique.
function onImgSizeChange(axis, val) {
    let v = Math.min(800, Math.max(20, parseInt(val) || 20));
    if (axis === 'w') {
        document.getElementById('imgWRange').value = v;
        document.getElementById('imgWNum').value   = v;
        if (selectedElement) selectedElement.style.width = v + 'px';
    } else {
        document.getElementById('imgHRange').value = v;
        document.getElementById('imgHNum').value   = v;
        if (selectedElement) selectedElement.style.height = v + 'px';
    }
}

// Modifie l'opacité de l'image sélectionnée en synchronisant slider et champ numérique.
function onImgOpacityChange(val) {
    let v = Math.min(100, Math.max(0, parseInt(val) || 0));
    document.getElementById('imgOpacityRange').value = v;
    document.getElementById('imgOpacityNum').value   = v;
    if (selectedElement && selectedElement.dataset.type === 'image') {
        selectedElement.style.opacity = v / 100;
    }
}
