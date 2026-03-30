<?php
session_start();

// Sauvegarder les éléments en session
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if (!isset($_SESSION['elements'])) {
        $_SESSION['elements'] = [];
    }
    
    if ($_POST['action'] === 'add') {
        $element = [
            'id' => uniqid(),
            'type' => $_POST['type'],
            'content' => $_POST['content'] ?? '',
            'x' => $_POST['x'] ?? 50,
            'y' => $_POST['y'] ?? 50,
            'width' => $_POST['width'] ?? 200,
            'height' => $_POST['height'] ?? 100,
            'color' => $_POST['color'] ?? '#000000'
        ];
        $_SESSION['elements'][] = $element;
    }
    
    if ($_POST['action'] === 'delete') {
        $_SESSION['elements'] = array_filter($_SESSION['elements'], 
            fn($el) => $el['id'] !== $_POST['id']);
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Créateur de Projet</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f0f0f0; }
        .container { display: flex; height: 100vh; }
        .toolbar { width: 250px; background: white; padding: 20px; border-right: 1px solid #ddd; overflow-y: auto; }
        .canvas { flex: 1; background: white; position: relative; margin: 20px; border: 2px solid #ddd; }
        .element { position: absolute; cursor: move; border: 2px solid #007bff; padding: 10px; }
        .element:hover { background: rgba(0,123,255,0.1); }
        .toolbar h3 { margin-bottom: 15px; }
        .toolbar label { display: block; margin: 10px 0 5px; font-weight: bold; }
        .toolbar input, .toolbar select { width: 100%; padding: 8px; margin-bottom: 10px; }
        button { background: #007bff; color: white; padding: 10px 15px; border: none; cursor: pointer; border-radius: 4px; width: 100%; margin-bottom: 10px; }
        button:hover { background: #0056b3; }
        .delete-btn { background: #dc3545; font-size: 12px; padding: 5px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="toolbar">
            <h3>Ajouter un élément</h3>
            <form method="POST">
                <input type="hidden" name="action" value="add">
                
                <label>Type :</label>
                <select name="type" required>
                    <option value="text">Texte</option>
                    <option value="rect">Rectangle</option>
                    <option value="circle">Cercle</option>
                </select>
                
                <label>Contenu :</label>
                <input type="text" name="content" placeholder="Votre texte...">
                
                <label>Couleur :</label>
                <input type="color" name="color" value="#000000">
                
                <button type="submit">Ajouter</button>
            </form>
        </div>
        
        <div class="canvas" id="canvas">
            <?php if (isset($_SESSION['elements'])): ?>
                <?php foreach ($_SESSION['elements'] as $elem): ?>
                    <div class="element" style="left: <?=$elem['x']?>px; top: <?=$elem['y']?>px; width: <?=$elem['width']?>px; height: <?=$elem['height']?>px; background: <?=$elem['color']?>;">
                        <?=$elem['content']?>
                        <form method="POST" style="margin-top: 5px;">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?=$elem['id']?>">
                            <button type="submit" class="delete-btn">Supprimer</button>
                        </form>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>