<?php
session_start();

define('EC2_URL', 'http://13.38.245.13');  
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accueil — Mes projets</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: Arial, sans-serif;
            min-height: 100vh;
            background: linear-gradient(135deg, #1a1a2e 0%, #c0392b 50%, #e8826a 100%);
            padding: 40px 20px;
        }

        .page {
            max-width: 900px;
            margin: 0 auto;
        }

        h1 {
            color: white;
            font-size: 28px;
            margin-bottom: 30px;
            text-align: center;
            text-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }

        h2 {
            color: white;
            font-size: 18px;
            margin: 30px 0 16px;
            opacity: 0.9;
        }

        .btn-create {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            width: 100%;
            padding: 16px;
            background: white;
            color: #667eea;
            font-size: 16px;
            font-weight: bold;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            text-decoration: none;
            box-shadow: 0 4px 15px rgba(0,0,0,0.15);
            transition: transform 0.15s, box-shadow 0.15s;
        }
        .btn-create:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0,0,0,0.2);
        }


        .projects-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 16px;
            margin-top: 8px;
        }

        .project-card {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.15);
            transition: transform 0.15s, box-shadow 0.15s;
            cursor: pointer;
            text-decoration: none;
            display: block;
        }
        .project-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.2);
        }

        .project-thumb {
            width: 100%;
            height: 150px;
            object-fit: cover;
            display: block;
            background: #f0f0f0;
        }

        .project-thumb-placeholder {
            width: 100%;
            height: 150px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f5f5f5;
            color: #aaa;
            font-size: 36px;
        }

        .project-info {
            padding: 12px;
        }
        .project-name {
            font-weight: bold;
            color: #333;
            font-size: 14px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .project-date {
            color: #999;
            font-size: 12px;
            margin-top: 4px;
        }


        .loading, .empty, .error {
            text-align: center;
            padding: 40px;
            background: rgba(255,255,255,0.15);
            border-radius: 10px;
            color: white;
        }
        .loading { font-size: 16px; }
        .empty   { font-size: 15px; opacity: 0.85; }
        .error   { background: rgba(220,53,69,0.3); font-size: 14px; }


        .logout {
            display: block;
            text-align: center;
            margin-top: 30px;
            color: rgba(255,255,255,0.7);
            text-decoration: none;
            font-size: 14px;
        }
        .logout:hover { color: white; }


        .modal-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.75);
            z-index: 1000;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .modal-overlay.open { display: flex; }
        .modal-box {
            background: white;
            border-radius: 12px;
            max-width: 700px;
            width: 100%;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0,0,0,0.4);
            display: flex;
            flex-direction: column;
            max-height: 90vh;
        }
        .modal-img {
            width: 100%;
            display: block;
            flex-shrink: 1;
            min-height: 0;
            object-fit: contain;
        }
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 16px 20px;
            border-bottom: 1px solid #eee;
            flex-shrink: 0;
        }
        .modal-header h3 { font-size: 16px; color: #333; }
        .modal-close {
            background: none; border: none; font-size: 22px;
            cursor: pointer; color: #888; line-height: 1;
        }
        .modal-close:hover { color: #333; }
        .modal-footer {
            padding: 14px 20px;
            display: flex;
            gap: 10px;
            border-top: 1px solid #eee;
            flex-shrink: 0;
            background: white;
            position: relative;
            z-index: 10;
        }
        
        .btn-edit {
            padding: 9px 18px;
            background: #2e7d32;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            text-decoration: none;
        }
        .btn-edit:hover { background: #1b5e20; }

        .btn-suppr {
            padding: 9px 18px;
            background: #ff0000;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            text-decoration: none;
        }
        .btn-suppr:hover { background: #c0392b; }
    </style>
</head>
<body>
<div class="page">
    <h1>🗂 Mes projets</h1>

    <a href="creer_projet.php" class="btn-create">
        ✏️ Créer un nouveau projet
    </a>

    <h2>📁 Projets sauvegardés</h2>

    <div id="projectsContainer">
        <div class="loading">⏳ Chargement des projets…</div>
    </div>

    <?php if (isset($_SESSION['user_email'])): ?>
        <a href="connexion.php" class="logout">Se déconnecter</a>
    <?php endif; ?>
</div>

<div class="modal-overlay" id="modalOverlay" onclick="closeModal(event)">
    <div class="modal-box">
        <div class="modal-header">
            <h3 id="modalTitle"></h3>
            <button class="modal-close" onclick="closeModalDirect()">✕</button>
        </div>
        <img class="modal-img" id="modalImg" src="" alt="Aperçu du projet">
        <div class="modal-footer">
            <a class="btn-edit" id="modalEdit" href="#">Modifier</a>
            <button class="btn-suppr" onclick="confirmDelete()">Supprimer</button>
            <span id="modalDate" style="align-self:center;color:#999;font-size:13px;margin-left:auto;"></span>
        </div>
    </div>
</div>

<!-- Modal confirmation suppression -->
<div class="confirm-overlay" id="confirmOverlay" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.6);z-index:2000;align-items:center;justify-content:center;">
    <div style="background:white;border-radius:10px;padding:28px;width:320px;box-shadow:0 8px 30px rgba(0,0,0,0.3);text-align:center;">
        <h3 style="font-size:16px;color:#333;margin-bottom:10px;">🗑 Supprimer ce projet ?</h3>
        <p id="confirmText" style="font-size:13px;color:#777;margin-bottom:20px;"></p>
        <div style="display:flex;gap:10px;">
            <button onclick="closeConfirm()" style="flex:1;padding:10px;border:none;border-radius:6px;background:#eee;color:#555;font-size:14px;font-weight:bold;cursor:pointer;">Annuler</button>
            <button id="btnConfirmDel" onclick="doDelete()" style="flex:1;padding:10px;border:none;border-radius:6px;background:#e53935;color:white;font-size:14px;font-weight:bold;cursor:pointer;">Supprimer</button>
        </div>
    </div>
</div>

<script>
    const EC2_URL    = '<?= EC2_URL ?>';
    const API_SECRET = 'MON_SECRET_12345';
    const USER_ID    = '<?= $_SESSION['user_id'] ?? '' ?>';
    let currentProjet = null;


    async function loadProjects() {
        const container = document.getElementById('projectsContainer');
        try {
            const res  = await fetch(EC2_URL + '/api_list.php?user_id=' + encodeURIComponent(USER_ID));
            const data = await res.json();

            if (!data.success || data.projets.length === 0) {
                container.innerHTML = '<div class="empty">Aucun projet sauvegardé pour l\'instant.<br>Créez votre premier projet !</div>';
                return;
            }

            const grid = document.createElement('div');
            grid.className = 'projects-grid';

            data.projets.forEach(p => {
                const card = document.createElement('div');
                card.className = 'project-card';
                card.onclick = () => openModal(p);
                card.innerHTML = `
                    <img class="project-thumb" src="${p.url_image}" alt="${p.nom}"
                         onerror="this.outerHTML='<div class=\\'project-thumb-placeholder\\'>🖼</div>'">
                    <div class="project-info">
                        <div class="project-name">${p.nom}</div>
                        <div class="project-date">📅 ${p.date}</div>
                    </div>`;
                grid.appendChild(card);
            });

            container.innerHTML = '';
            container.appendChild(grid);

        } catch (err) {
            container.innerHTML = `<div class="error">❌ Impossible de contacter le serveur EC2.<br><small>${err.message}</small><br><br>Vérifiez que l'EC2 est démarré et que l'IP est correcte dans index.php.</div>`;
        }
    }


    function openModal(projet) {
        currentProjet = projet;
        document.getElementById('modalTitle').textContent = projet.nom;
        document.getElementById('modalImg').src           = projet.url_image;
        document.getElementById('modalDate').textContent  = '📅 ' + projet.date;
        document.getElementById('modalEdit').href         = 'creer_projet.php?projet=' + encodeURIComponent(projet.nom);
        document.getElementById('modalOverlay').classList.add('open');
    }

    function closeModal(e) {
        if (e.target.id === 'modalOverlay') closeModalDirect();
    }
    function closeModalDirect() {
        document.getElementById('modalOverlay').classList.remove('open');
    }

    function confirmDelete() {
        if (!currentProjet) return;
        document.getElementById('confirmText').textContent = `Supprimer "${currentProjet.nom}" ? Cette action est irréversible.`;
        const overlay = document.getElementById('confirmOverlay');
        overlay.style.display = 'flex';
    }
    function closeConfirm() {
        document.getElementById('confirmOverlay').style.display = 'none';
    }
    async function doDelete() {
        if (!currentProjet) return;
        const btn = document.getElementById('btnConfirmDel');
        btn.disabled = true;
        btn.textContent = '⏳…';
        try {
            const res  = await fetch(EC2_URL + '/api_delete.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ nom: currentProjet.nom, user_id: USER_ID, secret: API_SECRET })
            });
            const data = await res.json();
            if (data.success) {
                closeConfirm();
                closeModalDirect();
                loadProjects();
            } else {
                alert('Erreur : ' + (data.error || 'Inconnue'));
            }
        } catch(e) {
            alert('Erreur réseau : ' + e.message);
        } finally {
            btn.disabled = false;
            btn.textContent = 'Supprimer';
        }
    }


    loadProjects();
</script>
</body>
</html>