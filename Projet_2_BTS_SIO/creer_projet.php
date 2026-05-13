<?php

$host = 'localhost';
$dbname = 'projet_2';
$user = 'root_copy';
$password = 'Spirit-FMP';

session_start();

define('EC2_URL', 'http://13.38.245.13');  

define('API_SECRET', 'MON_SECRET_12345');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Créer un projet - Feuille A4</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body { font-family: Arial, sans-serif; background: linear-gradient(135deg, #1a1a2e 0%, #c0392b 50%, #e8826a 100%); min-height: 100vh; padding: 20px; }

        .top-bar {
            display: flex;
            justify-content: flex-end;
            max-width: 1400px;
            margin: 0 auto 16px auto;
        }
        .btn-pdf, .btn-save {
            display: flex; align-items: center; gap: 8px;
            padding: 10px 22px; color: white; border: none;
            border-radius: 6px; font-size: 14px; font-weight: bold;
            cursor: pointer; box-shadow: 0 2px 8px rgba(0,0,0,0.25);
            transition: background 0.2s;
        }
        .btn-pdf  { background: #e53935; }
        .btn-pdf:hover    { background: #b71c1c; }
        .btn-pdf:disabled { background: #aaa; cursor: not-allowed; }
        .btn-save { background: #2e7d32; }
        .btn-save:hover    { background: #1b5e20; }
        .btn-save:disabled { background: #aaa; cursor: not-allowed; }

        /*Modal sauvegarde  */
        .save-overlay {
            display: none; position: fixed; inset: 0;
            background: rgba(0,0,0,0.5); z-index: 2000;
            align-items: center; justify-content: center;
        }
        .save-overlay.open { display: flex; }
        .save-popup {
            background: white; border-radius: 12px; padding: 28px;
            width: 360px; box-shadow: 0 8px 32px rgba(0,0,0,0.3);
        }
        .save-popup h3 { font-size: 17px; color: #333; margin-bottom: 16px; }
        .save-popup input[type="text"] {
            width: 100%; padding: 10px 12px; border: 1.5px solid #ccc;
            border-radius: 6px; font-size: 14px; outline: none; margin-bottom: 14px;
        }
        .save-popup input[type="text"]:focus { border-color: #2e7d32; }
        .save-popup-btns { display: flex; gap: 10px; }
        .save-popup-btns button {
            flex: 1; padding: 10px; border: none; border-radius: 6px;
            font-size: 14px; cursor: pointer; font-weight: bold;
        }
        .btn-confirm-save { background: #2e7d32; color: white; }
        .btn-confirm-save:hover { background: #1b5e20; }
        .btn-cancel-save  { background: #eee; color: #555; }
        .btn-cancel-save:hover { background: #ddd; }
        .save-status { margin-top: 12px; font-size: 13px; text-align: center; min-height: 18px; }

        .container { display: flex; gap: 20px; max-width: 1400px; margin: 0 auto; }

        .toolbar {
            width: 250px;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            height: fit-content;
        }

        .toolbar h3 { margin-bottom: 15px; color: #333; }

        .toolbar > button {
            width: 100%;
            padding: 10px;
            margin: 8px 0;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
        }

        .toolbar > button:hover { background-color: #0056b3; }

        .props-panel {
            display: none;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 2px solid #007bff;
        }

        .props-panel.visible { display: block; }

        .props-panel h4 {
            font-size: 12px;
            color: #007bff;
            margin-bottom: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .props-panel label {
            font-size: 12px;
            color: #555;
            display: block;
            margin-bottom: 4px;
            margin-top: 8px;
        }

        .size-row {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 4px;
        }

        .size-row input[type="range"] {
            flex: 1;
            margin: 0;
            padding: 0;
            border: none;
            accent-color: #007bff;
        }

        .size-row input[type="number"] {
            width: 52px;
            padding: 4px 6px;
            margin: 0;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 13px;
            text-align: center;
        }

        .color-row {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 4px;
        }

        .color-row input[type="color"] {
            width: 36px;
            height: 30px;
            border: 1px solid #ccc;
            border-radius: 4px;
            cursor: pointer;
            padding: 2px;
            background: white;
        }

        .color-row input[type="text"] {
            flex: 1;
            padding: 5px 8px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 12px;
            font-family: monospace;
        }

        .no-fill-row {
            display: flex;
            align-items: center;
            gap: 6px;
            margin-top: 4px;
            font-size: 12px;
            color: #555;
        }

        .info {
            font-size: 12px;
            color: #666;
            margin-top: 10px;
            padding-top: 10px;
            border-top: 1px solid #eee;
            line-height: 1.7;
        }

        .canvas-area { flex: 1; }

        .a4-page {
            width: 210mm;
            height: 297mm;
            background: white;
            position: relative;
            box-shadow: 0 0 20px rgba(0,0,0,0.2);
            margin: 0 auto;
            overflow: visible;
            isolation: isolate;
        }

        .element.behind {
            z-index: -1;
        }

        .element {
            position: absolute;
            cursor: move;
            touch-action: none;
            min-width: 20px;
            min-height: 20px;
            z-index: 1;
        }

        .element.selected { outline: 2px dashed #dc3545; outline-offset: 3px; }

        .element svg { display: block; width: 100%; height: 100%; overflow: visible; }

        .element-close {
            position: absolute;
            top: -10px;
            right: -10px;
            background: #dc3545;
            color: white;
            border: none;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            cursor: pointer;
            font-size: 12px;
            line-height: 20px;
            text-align: center;
            display: none;
            z-index: 10;
            padding: 0;
        }

        .element.selected .element-close { display: block; }
        .element-close:hover { background: #c82333; }

        .resize-handle {
            position: absolute;
            width: 10px;
            height: 10px;
            background: white;
            border: 2px solid #dc3545;
            border-radius: 50%;
            z-index: 10;
            display: none;
        }

        .element.selected .resize-handle { display: block; }

        .resize-handle.nw { top: -6px;  left: -6px;  cursor: nw-resize; }
        .resize-handle.n  { top: -6px;  left: calc(50% - 5px); cursor: n-resize; }
        .resize-handle.ne { top: -6px;  right: -6px; cursor: ne-resize; }
        .resize-handle.e  { top: calc(50% - 5px); right: -6px; cursor: e-resize; }
        .resize-handle.se { bottom: -6px; right: -6px; cursor: se-resize; }
        .resize-handle.s  { bottom: -6px; left: calc(50% - 5px); cursor: s-resize; }
        .resize-handle.sw { bottom: -6px; left: -6px;  cursor: sw-resize; }
        .resize-handle.w  { top: calc(50% - 5px); left: -6px;  cursor: w-resize; }

        .element textarea {
            width: 100%;
            height: 100%;
            margin: 0;
            padding: 0;
            resize: none;
            border: none;
            outline: none;
            font-family: Arial, sans-serif;
            font-size: 14px;
            line-height: 1.4;
            background: transparent;
            overflow: auto;
            cursor: text;
            display: block;
        }

        .shape-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.45);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }

        .shape-overlay.open { display: flex; }

        .shape-popup {
            background: white;
            border-radius: 12px;
            padding: 24px;
            width: 380px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.25);
        }

        .shape-popup-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 18px;
        }

        .shape-popup-header h3 { font-size: 16px; color: #333; margin: 0; }

        .shape-popup-header button {
            background: none;
            border: none;
            font-size: 22px;
            cursor: pointer;
            color: #888;
            line-height: 1;
            padding: 0;
            width: auto;
            margin: 0;
        }

        .shape-popup-header button:hover { color: #333; background: none; }

        .shape-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 10px;
        }

        .shape-btn {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 6px;
            padding: 12px 6px;
            border: 1.5px solid #e0e0e0;
            border-radius: 8px;
            background: #fafafa;
            cursor: pointer;
            font-size: 11px;
            color: #444;
            width: 100%;
            margin: 0;
            transition: border-color 0.15s, background 0.15s;
        }

        .shape-btn:hover {
            border-color: #007bff;
            background: #e8f0fe;
            color: #007bff;
        }

        .shape-btn svg { display: block; pointer-events: none; }

        .shape-clip-btn {
            padding: 6px 4px;
            border: 1.5px solid #e0e0e0;
            border-radius: 6px;
            background: #fafafa;
            cursor: pointer;
            font-size: 11px;
            color: #444;
            transition: border-color 0.15s, background 0.15s;
            width: 100%;
            margin: 0;
        }
        .shape-clip-btn:hover, .shape-clip-btn.active {
            border-color: #007bff;
            background: #e8f0fe;
            color: #007bff;
        }
    </style>
</head>
<body>
<div class="top-bar">
    <button class="btn-save" id="btnSave" onclick="openSaveModal()">Sauvegarder</button>
    <button class="btn-pdf"  id="btnPdf"  onclick="exportPDF()">Enregistrer en PDF</button>
</div>

<!-- Modal sauvegarde -->
<div class="save-overlay" id="saveOverlay" onclick="closeSaveModalOnOverlay(event)">
    <div class="save-popup">
        <h3>Sauvegarder le projet</h3>
        <input type="text" id="saveNom" placeholder="Nom du projet…" maxlength="80">
        <div class="save-popup-btns">
            <button class="btn-cancel-save"  onclick="closeSaveModal()">Annuler</button>
            <button class="btn-confirm-save" id="btnConfirmSave" onclick="doSave()">Sauvegarder</button>
        </div>
        <div class="save-status" id="saveStatus"></div>
    </div>
</div>
<div class="container">

    <div class="toolbar">
        <h3>Ajouter des éléments</h3>
        <button onclick="addTextBox()">Texte</button>
        <button onclick="openShapePopup()">Forme</button>
        <button onclick="triggerImageUpload()">Image</button>
        <input type="file" id="imageFileInput" accept="image/*" style="display:none" onchange="onImageFileSelected(event)">

        <!-- Propriétés IMAGE -->
        <div class="props-panel" id="imageProps">
            <h4>Propriétés de l'image</h4>
            <button onclick="triggerImageReplace()" style="width:100%;padding:7px;margin-bottom:8px;background:#007bff;color:white;border:none;border-radius:4px;cursor:pointer;font-size:12px;">🖼 Changer l'image</button>

            <label>Largeur (px)</label>
            <div class="size-row">
                <input type="range"  id="imgWRange" min="20" max="800" value="150" step="1"
                       oninput="onImgSizeChange('w', this.value)">
                <input type="number" id="imgWNum"   min="20" max="800" value="150"
                       oninput="onImgSizeChange('w', this.value)">
            </div>

            <label>Hauteur (px)</label>
            <div class="size-row">
                <input type="range"  id="imgHRange" min="20" max="800" value="100" step="1"
                       oninput="onImgSizeChange('h', this.value)">
                <input type="number" id="imgHNum"   min="20" max="800" value="100"
                       oninput="onImgSizeChange('h', this.value)">
            </div>

            <label>Forme</label>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:6px;margin-top:4px;">
                <button class="shape-clip-btn" onclick="setImgClip('none')" data-clip="none">▭ Rectangle</button>
                <button class="shape-clip-btn" onclick="setImgClip('circle')" data-clip="circle">● Cercle</button>
            </div>

            <label>Opacité</label>
            <div class="size-row">
                <input type="range"  id="imgOpacityRange" min="0" max="100" value="100" step="1"
                       oninput="onImgOpacityChange(this.value)">
                <input type="number" id="imgOpacityNum"   min="0" max="100" value="100"
                       oninput="onImgOpacityChange(this.value)">
            </div>
        </div>

        <!-- Propriétés TEXTE -->
        <div class="props-panel" id="textProps">
            <h4>Propriétés du texte</h4>
            <label>Taille de la police</label>
            <div class="size-row">
                <input type="range"  id="fontSizeRange" min="8" max="96" value="14" step="1"
                       oninput="onFontSizeChange(this.value)">
                <input type="number" id="fontSizeNum"   min="8" max="96" value="14"
                       oninput="onFontSizeChange(this.value)">
            </div>
            <div class="no-fill-row" style="margin-top:10px;">
                <input type="checkbox" id="textTransparent" checked onchange="onTextBgChange()">
                <label for="textTransparent" style="margin:0;">Fond transparent</label>
            </div>
            <div id="textBgColorRow" style="display:none;">
                <label>Couleur de fond</label>
                <div class="color-row">
                    <input type="color" id="textBgColor" value="#ffffff" oninput="onTextBgColorChange(this.value)">
                    <input type="text"  id="textBgHex"   value="#ffffff" maxlength="7"
                           oninput="if(/^#[0-9a-fA-F]{6}$/.test(this.value)){document.getElementById('textBgColor').value=this.value;onTextBgColorChange(this.value);}">
                </div>
            </div>
        </div>

        <div class="props-panel" id="shapeProps">
            <h4>Propriétés de la forme</h4>

            <label>Largeur (px)</label>
            <div class="size-row">
                <input type="range"  id="shapeWRange" min="20" max="600" value="150" step="1"
                       oninput="onShapeSizeChange('w', this.value)">
                <input type="number" id="shapeWNum"   min="20" max="600" value="150"
                       oninput="onShapeSizeChange('w', this.value)">
            </div>

            <label>Hauteur (px)</label>
            <div class="size-row">
                <input type="range"  id="shapeHRange" min="20" max="600" value="100" step="1"
                       oninput="onShapeSizeChange('h', this.value)">
                <input type="number" id="shapeHNum"   min="20" max="600" value="100"
                       oninput="onShapeSizeChange('h', this.value)">
            </div>

            <div id="fillColorRow">
                <label>Couleur de remplissage</label>
                <div class="color-row">
                    <input type="color" id="shapeFillColor" value="#e7f3ff" oninput="onShapeColorChange()">
                    <input type="text"  id="shapeFillHex"   value="#e7f3ff" maxlength="7"
                           oninput="onShapeHexChange('fill', this.value)">
                </div>
                <div class="no-fill-row">
                    <input type="checkbox" id="noFill" onchange="onNoFillChange()">
                    <label for="noFill" style="margin:0;">Transparent (sans remplissage)</label>
                </div>
            </div>

            <div id="strokeColorRow">
                <label>Couleur de bordure</label>
                <div class="color-row">
                    <input type="color" id="shapeStrokeColor" value="#0066cc" oninput="onShapeColorChange()">
                    <input type="text"  id="shapeStrokeHex"   value="#0066cc" maxlength="7"
                           oninput="onShapeHexChange('stroke', this.value)">
                </div>
            </div>

            <label>Épaisseur bordure</label>
            <div class="size-row">
                <input type="range"  id="strokeWRange" min="0" max="20" value="2" step="1"
                       oninput="onStrokeWidthChange(this.value)">
                <input type="number" id="strokeWNum"   min="0" max="20" value="2"
                       oninput="onStrokeWidthChange(this.value)">
            </div>
        </div>
    </div>

    <div class="canvas-area">
        <div class="a4-page" id="canvas"></div>
    </div>
</div>

<div class="shape-overlay" id="shapeOverlay" onclick="closeShapePopupOnOverlay(event)">
    <div class="shape-popup">
        <div class="shape-popup-header">
            <h3>Choisir une forme</h3>
            <button onclick="closeShapePopup()">✕</button>
        </div>
        <div class="shape-grid">

            <button class="shape-btn" onclick="addShape('rectangle')">
                <svg width="44" height="32" viewBox="0 0 44 32"><rect x="2" y="2" width="40" height="28" rx="2" fill="#e7f3ff" stroke="#0066cc" stroke-width="2"/></svg>
                Rectangle
            </button>

            <button class="shape-btn" onclick="addShape('square')">
                <svg width="36" height="36" viewBox="0 0 36 36"><rect x="2" y="2" width="32" height="32" rx="2" fill="#e7f3ff" stroke="#0066cc" stroke-width="2"/></svg>
                Carré
            </button>

            <button class="shape-btn" onclick="addShape('circle')">
                <svg width="36" height="36" viewBox="0 0 36 36"><circle cx="18" cy="18" r="16" fill="#ffe7e7" stroke="#cc0000" stroke-width="2"/></svg>
                Cercle
            </button>

            <button class="shape-btn" onclick="addShape('ellipse')">
                <svg width="44" height="30" viewBox="0 0 44 30"><ellipse cx="22" cy="15" rx="20" ry="12" fill="#ffe7e7" stroke="#cc0000" stroke-width="2"/></svg>
                Ellipse
            </button>

            <button class="shape-btn" onclick="addShape('triangle')">
                <svg width="36" height="36" viewBox="0 0 36 36"><polygon points="18,2 34,34 2,34" fill="#fff9e0" stroke="#cc8800" stroke-width="2"/></svg>
                Triangle
            </button>

            <button class="shape-btn" onclick="addShape('diamond')">
                <svg width="36" height="36" viewBox="0 0 36 36"><polygon points="18,2 34,18 18,34 2,18" fill="#f0e8ff" stroke="#6600cc" stroke-width="2"/></svg>
                Losange
            </button>

            <button class="shape-btn" onclick="addShape('pentagon')">
                <svg width="36" height="36" viewBox="0 0 36 36"><polygon points="18,2 34,14 28,32 8,32 2,14" fill="#e8fff0" stroke="#006633" stroke-width="2"/></svg>
                Pentagone
            </button>

            <button class="shape-btn" onclick="addShape('star')">
                <svg width="36" height="36" viewBox="0 0 36 36"><polygon points="18,2 22,13 34,13 24,20 28,32 18,25 8,32 12,20 2,13 14,13" fill="#fff0e8" stroke="#cc4400" stroke-width="2"/></svg>
                Étoile
            </button>

            <button class="shape-btn" onclick="addShape('arrow_full')">
                <svg width="44" height="30" viewBox="0 0 44 30"><polygon points="2,10 28,10 28,2 42,15 28,28 28,20 2,20" fill="#e0f0ff" stroke="#0055aa" stroke-width="2"/></svg>
                Flèche
            </button>

            <button class="shape-btn" onclick="addShape('arrow_outline')">
                <svg width="44" height="30" viewBox="0 0 44 30">
                    <polyline points="2,15 34,15" fill="none" stroke="#0055aa" stroke-width="2.5" stroke-linecap="round"/>
                    <polygon points="27,8 42,15 27,22" fill="none" stroke="#0055aa" stroke-width="2.5" stroke-linejoin="round"/>
                </svg>
                Flèche simple
            </button>

            <button class="shape-btn" onclick="addShape('line')">
                <svg width="44" height="20" viewBox="0 0 44 20"><line x1="2" y1="10" x2="42" y2="10" stroke="#333" stroke-width="2.5" stroke-linecap="round"/></svg>
                Ligne
            </button>

        </div>
    </div>
</div>

<script>

    // ============================================================
    // VARIABLES GLOBALES
    // ============================================================
    const canvas           = document.getElementById('canvas');
    const textProps        = document.getElementById('textProps');
    const shapeProps       = document.getElementById('shapeProps');
    const fontSizeRange    = document.getElementById('fontSizeRange');
    const fontSizeNum      = document.getElementById('fontSizeNum');
    const shapeWRange      = document.getElementById('shapeWRange');
    const shapeWNum        = document.getElementById('shapeWNum');
    const shapeHRange      = document.getElementById('shapeHRange');
    const shapeHNum        = document.getElementById('shapeHNum');
    const shapeFillColor   = document.getElementById('shapeFillColor');
    const shapeFillHex     = document.getElementById('shapeFillHex');
    const shapeStrokeColor = document.getElementById('shapeStrokeColor');
    const shapeStrokeHex   = document.getElementById('shapeStrokeHex');
    const strokeWRange     = document.getElementById('strokeWRange');
    const strokeWNum       = document.getElementById('strokeWNum');
    const noFillChk        = document.getElementById('noFill');
    const fillColorRow     = document.getElementById('fillColorRow');
    const textTransparent  = document.getElementById('textTransparent');
    const textBgColorRow   = document.getElementById('textBgColorRow');
    const textBgColor      = document.getElementById('textBgColor');
    const imageProps       = document.getElementById('imageProps');

    const EC2_URL    = '<?= EC2_URL ?>';
    const API_SECRET = '<?= API_SECRET ?>';
    const USER_ID    = '<?= $_SESSION['user_id'] ?? '' ?>';

    let selectedElement = null;
    let isDragging      = false;
    let dragOffset      = { x: 0, y: 0 };
    let isResizing      = false;
    let resizeDir       = '';
    let resizeStart     = {};
    let pendingImageReplace = false;


    // ============================================================
    // CANVAS — sélection, drag & drop, redimensionnement
    // ============================================================

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

    // Désélectionne l'élément actif en cliquant sur le fond du canvas.
    canvas.addEventListener('mousedown', function(e) {
        if (e.target === canvas) {
            if (selectedElement) {
                selectedElement.classList.remove('selected');
                selectedElement = null;
                updatePropsPanel(null);
            }
        }
    });

    // Supprime l'élément du canvas et réinitialise la sélection et le panneau de propriétés.
    function deleteElement(e) {
        e.stopPropagation();
        e.currentTarget.closest('.element').remove();
        selectedElement = null;
        updatePropsPanel(null);
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

    // Déplace l'élément sélectionné en suivant la souris.
    function drag(e) {
        if (!isDragging || !selectedElement) return;
        const cr = canvas.getBoundingClientRect();
        let x = e.clientX - cr.left - dragOffset.x;
        let y = e.clientY - cr.top  - dragOffset.y;
        selectedElement.style.left = x + 'px';
        selectedElement.style.top  = y + 'px';
        updateZIndex(selectedElement, x, y, cr);
    }

    // Arrête le déplacement.
    function stopDrag() {
        isDragging = false;
        document.removeEventListener('mousemove', drag);
        document.removeEventListener('mouseup',   stopDrag);
    }

    // Place l'élément derrière le canvas s'il sort complètement de la zone de travail.
    function updateZIndex(el, x, y, cr) {
        if (!cr) { const r = canvas.getBoundingClientRect(); cr = { width: r.width, height: r.height }; }
        const w  = el.offsetWidth  || 0;
        const h  = el.offsetHeight || 0;
        const outside = (x + w <= 0) || (y + h <= 0) || (x >= cr.width) || (y >= cr.height);
        el.classList.toggle('behind', outside);
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

    // Redimensionne l'élément en temps réel selon la direction de la poignée.
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

    // Arrête le redimensionnement.
    function stopResize() {
        isResizing = false;
        document.removeEventListener('mousemove', doResize);
        document.removeEventListener('mouseup',   stopResize);
    }


    // ============================================================
    // FORMES SVG
    // ============================================================

    // Catalogue de toutes les formes disponibles avec leurs dimensions et leur SVG interne.
    const SHAPE_DEFS = {
        rectangle:     { w: 150, h: 100, hasFill: true,
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


    // ============================================================
    // TEXTE
    // ============================================================

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


    // ============================================================
    // IMAGES
    // ============================================================

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


    // ============================================================
    // PANNEAU DE PROPRIÉTÉS
    // ============================================================

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


    // ============================================================
    // SAUVEGARDE & EXPORT
    // ============================================================

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
        statusEl.textContent = 'Capture en cours…';
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

            statusEl.textContent = 'Envoi au serveur…';
            const res  = await fetch(EC2_URL + '/api_save.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ nom, image: imageB64, elements, user_id: USER_ID, secret: API_SECRET })
            });
            const data = await res.json();

            if (data.success) {
                statusEl.style.color = '#2e7d32';
                statusEl.textContent = 'Projet sauvegardé avec succès !';
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
        btn.textContent = 'Génération…';
        const prevSelected = selectedElement;
        if (selectedElement) selectedElement.classList.remove('selected');

        try {
            const { jsPDF } = window.jspdf;
            const pageEl = document.getElementById('canvas');
            const canvasImg = await html2canvas(pageEl, {
                scale: 2, useCORS: true, backgroundColor: '#ffffff', logging: false,
                onclone: function(doc) {
                    doc.querySelectorAll('.element-close, .resize-handle').forEach(el => el.style.display = 'none');
                    doc.querySelectorAll('.element.behind').forEach(el => el.style.display = 'none');
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
            btn.innerHTML = 'Enregistrer en PDF';
            if (prevSelected) prevSelected.classList.add('selected');
        }
    }

</script>
</body>
</html>