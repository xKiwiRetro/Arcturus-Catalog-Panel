<?php
// api.php - MULTI LANGUAGE READY xKiwi XaloHotel v1
ini_set('display_errors', 0);
error_reporting(E_ALL);
ob_start(); 
require 'db.php'; // Lädt jetzt auch $L (Sprache) xKiwi XaloHotel v1

function sendJSON($data) {
    ob_end_clean();
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data);
    exit;
}

$action = isset($_POST['action']) ? $_POST['action'] : '';

function buildSidebarHtml($elements, $parentId) {
    global $L; // Zugriff auf Sprache xKiwi XaloHotel v1
    $html = '';
    foreach ($elements as $el) {
        if ($el['parent_id'] == $parentId) {
            $html .= '<li class="dd-item" data-id="' . $el['id'] . '">';
            $html .= '<div class="dd-handle-custom">';
            
            $hasChildren = false;
            foreach($elements as $check) { if($check['parent_id'] == $el['id']) { $hasChildren = true; break; } }
            
            if($hasChildren) {
                $html .= '<span class="arrow-box" onmousedown="event.stopPropagation()" onclick="toggleFolder(this, event)"><i class="fas fa-caret-right"></i></span>';
            } else {
                $html .= '<span class="arrow-box"></span>';
            }

            $iconClass = ($el['icon_image'] > 0) ? 'fa-cube' : 'fa-folder';
            $html .= '<div class="content-box" onclick="loadPage(' . $el['id'] . ')">';
            $html .= '<i class="fas ' . $iconClass . '" style="margin-right:8px; opacity:0.7;"></i>';
            $html .= '<span class="folder-name">' . htmlspecialchars($el['caption']) . '</span>';
            $html .= '</div>';
            
            $html .= '</div>'; 
            $childrenHtml = buildSidebarHtml($elements, $el['id']);
            if (!empty($childrenHtml)) {
                $html .= '<ol class="dd-list" style="display:none;">' . $childrenHtml . '</ol>';
            }
            $html .= '</li>';
        }
    }
    return $html;
}

if ($action == 'get_tree') {
    ob_end_clean();
    $parentId = $_POST['parent_id'];
    try {
        $stmt = $pdo->query("SELECT id, parent_id, caption, icon_image FROM catalog_pages ORDER BY order_num ASC");
        $allPages = $stmt->fetchAll();
        $output = buildSidebarHtml($allPages, $parentId);
        
        if (empty($output)) {
            // SPRACHVARIABLE GENUTZT: xKiwi XaloHotel v1
            echo '<div style="padding:20px; text-align:center; color:#999; font-size:12px;">'.$L['empty_folder'].'<br><br><button class="btn-save" style="width:auto; padding:5px 10px; font-size:11px;" onclick="createSubPage('.$parentId.')">'.$L['create_first'].'</button></div>';
        } else {
            echo '<ol class="dd-list">' . $output . '</ol>';
        }
    } catch (Exception $e) {
        echo '<div style="color:red; padding:10px;">'.$L['error_db'] . $e->getMessage() . '</div>';
    }
    exit;
}

if ($action == 'get_page') {
    try {
        $stmt = $pdo->prepare("SELECT * FROM catalog_pages WHERE id = ?");
        $stmt->execute([$_POST['id']]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if(!$data) { sendJSON(['error' => $L['error_not_found']]); }

        try {
            $cStmt = $pdo->prepare("SELECT COUNT(*) FROM catalog_items WHERE page_id = ?");
            $cStmt->execute([$_POST['id']]);
            $data['item_count'] = $cStmt->fetchColumn();
        } catch(Exception $ex) { $data['item_count'] = 0; }

        sendJSON($data);
    } catch (Exception $e) { sendJSON(['error' => $L['error_db'] . $e->getMessage()]); }
}

if ($action == 'update') {
    try {
        $layout = empty($_POST['page_layout']) ? 'default_3x3' : $_POST['page_layout'];
        $enabled = isset($_POST['enabled']) ? '1' : '0';
        $visible = isset($_POST['visible']) ? '1' : '0';
        $text2 = isset($_POST['page_text2']) ? $_POST['page_text2'] : '';
        $details = isset($_POST['page_text_details']) ? $_POST['page_text_details'] : '';

        $sql = "UPDATE catalog_pages SET 
                caption=?, parent_id=?, icon_image=?, page_layout=?, min_rank=?, order_num=?, 
                page_headline=?, page_teaser=?, page_text1=?, 
                enabled=?, visible=?";
        
        // Prüfen ob Spalten existieren (simpler Fix: einfach anhängen und hoffen, sonst Fehler fangen)xKiwi XaloHotel v1
        $sql .= ", page_text2=?, page_text_details=? WHERE id=?";
        
        $pdo->prepare($sql)->execute([
            $_POST['caption'], $_POST['parent_id'], $_POST['icon_image'], $layout, 
            $_POST['min_rank'], $_POST['order_num'], $_POST['page_headline'], $_POST['page_teaser'], 
            $_POST['page_text1'], $enabled, $visible, $text2, $details, $_POST['id']
        ]);
        
        sendJSON(['status' => 'success']);
    } catch (Exception $e) { sendJSON(['status' => 'error', 'msg' => $e->getMessage()]); }
}

if ($action == 'sort') {
    ob_end_clean();
    $tree = json_decode($_POST['tree'], true);
    $rootParent = $_POST['parent_id']; 

    function saveOrder($list, $parentId, $pdo) {
        foreach ($list as $key => $item) {
            $sql = "UPDATE catalog_pages SET order_num = ?, parent_id = ? WHERE id = ?";
            $pdo->prepare($sql)->execute([$key, $parentId, $item['id']]);
            if (isset($item['children'])) {
                saveOrder($item['children'], $item['id'], $pdo);
            }
        }
    }
    
    if($tree && $rootParent) {
        saveOrder($tree, $rootParent, $pdo);
    }
    echo "OK"; exit;
}

if ($action == 'sort_tabs') {
    $list = $_POST['list'];
    if(is_array($list)) {
        foreach ($list as $index => $id) {
            $pdo->prepare("UPDATE catalog_pages SET order_num = ? WHERE id = ?")->execute([$index, $id]);
        }
    }
    echo "OK"; exit;
}

if ($action == 'move_to_tab') {
    try {
        $pdo->prepare("UPDATE catalog_pages SET parent_id = ? WHERE id = ?")->execute([$_POST['new_parent'], $_POST['id']]);
        sendJSON(['status' => 'success']);
    } catch (Exception $e) { sendJSON(['status' => 'error', 'msg' => $e->getMessage()]); }
}

if ($action == 'create_child' || $action == 'create_root') {
    $pid = ($action == 'create_root') ? -1 : $_POST['parent_id']; 
    $pdo->prepare("INSERT INTO catalog_pages (parent_id, caption, page_layout, icon_image, min_rank, enabled, visible, order_num) VALUES (?, ?, 'default_3x3', 1, 1, '1', '1', 999)")->execute([$pid, $_POST['caption']]);
    exit;
}
if ($action == 'delete') {
    $pdo->prepare("DELETE FROM catalog_pages WHERE id = ?")->execute([$_POST['id']]);
    exit;
}
?>