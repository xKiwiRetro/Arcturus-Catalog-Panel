<?php
// index.php - FINAL MULTI-LANGUAGE
ini_set('display_errors', 1);
error_reporting(E_ALL);
require 'db.php'; // Lädt DB + CONFIG + LANGUAGES ($L)

try {
    $stmt = $pdo->prepare("SELECT * FROM catalog_pages WHERE parent_id = -1 ORDER BY order_num ASC");
    $stmt->execute();
    $tabs = $stmt->fetchAll();
    if(count($tabs) == 0) {
        $stmt = $pdo->prepare("SELECT * FROM catalog_pages WHERE parent_id = 0 ORDER BY order_num ASC");
        $stmt->execute();
        $tabs = $stmt->fetchAll();
    }
} catch (Exception $e) { die("DB Fehler: " . $e->getMessage()); }
$activeTabId = isset($_GET['tab']) ? $_GET['tab'] : (isset($tabs[0]['id']) ? $tabs[0]['id'] : 0);
?>
<!DOCTYPE html>
<html lang="<?= $language ?>">
<head>
    <meta charset="UTF-8">
    <title><?= $L['title'] ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/nestable2/1.6.0/jquery.nestable.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/nestable2/1.6.0/jquery.nestable.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.15.0/Sortable.min.js"></script>
    
    <style>
        body { background: #1a1a1a; font-family: 'Segoe UI', sans-serif; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; color: #333; }
        .catalog-window { width: 1400px; height: 900px; background: #f4f4f4; border-radius: 8px; box-shadow: 0 25px 60px rgba(0,0,0,0.6); display: flex; flex-direction: column; overflow: hidden; border: 1px solid #333; }
        .header { background: #d35400; height: 50px; display: flex; align-items: center; padding: 0 20px; color: white; font-weight: bold; border-bottom: 4px solid #a04000; justify-content: space-between; }
        
        .tabs-bar { background: #2c3e50; padding: 10px 15px 0 15px; display: flex; gap: 5px; overflow-x: auto; align-items: flex-end; }
        #tabs-sortable-container { display:flex; gap:5px; overflow-x:auto; align-items: flex-end; }
        .tab-item { background: #34495E; color: #bdc3c7; padding: 8px 20px; border-radius: 6px 6px 0 0; text-decoration: none; font-size: 13px; font-weight: bold; display: flex; align-items: center; gap: 8px; border: 1px solid #222; border-bottom: none; flex-shrink: 0; transition:0.2s; cursor: grab; position: relative; }
        .tab-item:hover { background: #455d75; color: white; }
        .tab-item.active { background: #f4f4f4; color: #2c3e50; height: 40px; border-top: 4px solid #d35400; font-size:14px; cursor: default; }
        .tab-item.drag-hover { background: #27ae60 !important; color: white !important; transform: scale(1.1); z-index: 10; box-shadow: 0 0 15px rgba(39, 174, 96, 0.8); }

        .top-btn { margin-left: auto; color: white; border: none; padding: 6px 15px; border-radius: 4px; cursor: pointer; font-size: 11px; font-weight: bold; margin-bottom: 5px; margin-left: 10px; display: flex; align-items: center; gap: 5px; }
        .btn-sort { background: #3498DB; } .btn-sort.active { background: #e67e22; box-shadow: inset 0 3px 5px rgba(0,0,0,0.2); }
        .btn-edit-tab { background: #27ae60; }
        .btn-new-tab { background: #27AE60; color:white; padding:8px 15px; border-radius:4px; font-weight:bold; text-decoration:none; margin-left:5px; margin-bottom: 2px;}

        .content-area { display: flex; flex: 1; height: 100%; overflow: hidden; }
        .sidebar { width: 350px; min-width: 350px; background: #e0e0e0; border-right: 1px solid #ccc; display: flex; flex-direction: column; padding: 0; }
        .search-container { padding: 10px; background: #dcdcdc; border-bottom: 1px solid #ccc; }
        .search-input { width: 100%; padding: 10px; border: 1px solid #bbb; border-radius: 4px; box-sizing: border-box; font-size: 14px; }
        .sidebar-content { flex: 1; overflow-y: auto; padding: 10px; }

        .dd-list { list-style: none !important; padding: 0 !important; margin: 0 !important; }
        .dd-item { margin-bottom: 2px; }
        .dd-empty, .dd-placeholder { display: none; } 
        .dd-handle-custom { background: #fff; border: 1px solid #ccc; border-radius: 4px; display: flex; align-items: stretch; transition: all 0.1s; height: 38px; cursor: default; }
        .dd-handle-custom:hover { border-color: #3498DB; background: #f0f8ff; }
        .arrow-box { width: 30px; display: flex; align-items: center; justify-content: center; cursor: pointer; color: #888; border-right: 1px solid #eee; background: #f9f9f9; border-radius: 4px 0 0 4px; }
        .arrow-box:hover { background: #eee; color: #333; }
        .arrow-box.open i { transform: rotate(90deg); color: #d35400; }
        .content-box { flex: 1; display: flex; align-items: center; padding: 0 10px; cursor: pointer; font-size: 13px; font-weight: 600; color: #444; }
        .content-box:hover { color: #3498DB; }
        .active-row .dd-handle-custom { border-left: 5px solid #d35400; background: #fff; }
        .active-row .content-box { color: #d35400; }
        body.drag-mode-active .dd-handle-custom { cursor: move !important; border-color: #e67e22; background: #fffdf0; }
        body.drag-mode-active .arrow-box { opacity: 0.3; pointer-events: none; }
        body.drag-mode-active .content-box { pointer-events: none; }

        .main-view { flex: 1; padding: 40px; overflow-y: auto; background: #f4f4f4; background-image: radial-gradient(#ddd 1px, transparent 1px); background-size: 20px 20px; }
        .editor-card { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 5px 20px rgba(0,0,0,0.1); display: none; max-width: 900px; margin: 0 auto; border: 1px solid #e0e0e0; }
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .full-width { grid-column: span 2; }
        label { display: block; font-size: 11px; font-weight: 800; color: #95a5a6; margin-bottom: 6px; text-transform: uppercase; }
        input, select, textarea { width: 100%; padding: 12px; border: 2px solid #ecf0f1; border-radius: 6px; font-size: 14px; box-sizing: border-box; background: #fafafa; }
        input:focus { border-color: #3498DB; outline: none; background: white; }
        .btn-save { background: #27AE60; color: white; border: none; padding: 15px; width: 100%; border-radius: 6px; font-weight: bold; cursor: pointer; margin-top: 25px; font-size: 14px; text-transform: uppercase; }
        .image-preview { height: 80px; background: #2c3e50; border-radius: 4px; margin-top: 5px; display: flex; align-items: center; justify-content: center; overflow: hidden; position: relative; border: 2px solid #bdc3c7; }
        .image-preview img { max-height: 100%; max-width: 100%; }
        .image-preview img:after { content: "❌ BILD FEHLT"; color: red; font-size: 10px; }
        .stat-badge { background: #e74c3c; color: white; font-size: 10px; padding: 3px 8px; border-radius: 10px; margin-left: 10px; }
    </style>
</head>
<body>

<div class="catalog-window">
    <div class="header">
        <div class="header-title"><i class="fas fa-crown"></i> &nbsp; <?= $L['title'] ?></div>
    </div>

    <div class="tabs-bar">
        <div id="tabs-sortable-container">
            <?php foreach($tabs as $tab): ?>
                <a href="?tab=<?= $tab['id'] ?>" class="tab-item tab-drop-target <?= $activeTabId == $tab['id'] ? 'active' : '' ?>" data-id="<?= $tab['id'] ?>" data-name="<?= htmlspecialchars($tab['caption']) ?>">
                    <i class="fas fa-folder"></i> <?= htmlspecialchars($tab['caption']) ?>
                </a>
            <?php endforeach; ?>
        </div>
        <a href="#" onclick="createNewRoot()" class="btn-new-tab">+</a>
        <div style="margin-left:auto; display:flex; align-items:center;">
            <button id="sortToggleBtn" onclick="toggleSortMode()" class="top-btn btn-sort"><i class="fas fa-arrows-alt"></i> <?= $L['btn_sort'] ?></button>
            <button onclick="editCurrentTab()" class="top-btn btn-edit-tab"><i class="fas fa-pencil-alt"></i> <?= $L['tab_edit'] ?></button>
        </div>
    </div>

    <div class="content-area">
        <div class="sidebar">
            <div class="search-container">
                <input type="text" id="searchInput" class="search-input" placeholder="<?= $L['search'] ?>" onkeyup="filterSidebar()">
            </div>
            <div class="sidebar-content">
                <div id="loading-spinner" style="text-align:center; padding:20px; color:#777;"><i class="fas fa-circle-notch fa-spin"></i> <?= $L['loading'] ?></div>
                <div class="dd" id="nestable-sidebar"></div>
            </div>
            <div style="padding:10px; border-top:1px solid #ccc; background:#e0e0e0;">
                <button class="btn-save" style="margin:0; padding:10px; background:#fff; color:#333; border:1px solid #ccc;" onclick="createSubPage(<?= $activeTabId ?>)"><?= $L['btn_new_sub'] ?></button>
            </div>
        </div>

        <div class="main-view">
            <div id="welcome-msg" style="text-align:center; margin-top:150px; color:#aaa;">
                <i class="fas fa-mouse-pointer" style="font-size:50px; margin-bottom:15px; color:#ccc;"></i><br>
                <h3>Wähle links eine Kategorie</h3>
            </div>

            <form id="editForm" class="editor-card">
                <div style="display:flex; justify-content:space-between; border-bottom:1px solid #eee; padding-bottom:15px; margin-bottom:20px;">
                    <h2 style="margin:0; font-size:18px; color:#2c3e50;"><?= $L['edit_page'] ?> <span id="display-id" style="font-size:12px; color:#999; font-weight:normal;"></span></h2>
                    <span id="item-count-badge" class="stat-badge" style="background:#bdc3c7">0 <?= $L['items'] ?></span>
                </div>
                
                <input type="hidden" name="id" id="inp_id">
                <input type="hidden" name="action" value="update">

                <div class="form-grid">
                    <div class="full-width">
                        <label><?= $L['lbl_caption'] ?></label>
                        <input type="text" name="caption" id="inp_caption">
                    </div>

                    <div><label><?= $L['lbl_order'] ?></label><input type="number" name="order_num" id="inp_order"></div>
                    <div><label><?= $L['lbl_parent'] ?></label><input type="number" name="parent_id" id="inp_parent" placeholder="<?= $L['ph_parent'] ?>"></div>

                    <div>
                        <label><?= $L['lbl_icon'] ?></label>
                        <input type="number" name="icon_image" id="inp_icon" onkeyup="updateIconPreview()" onchange="updateIconPreview()">
                        <div class="image-preview" style="height:40px; background:#fff; border:1px solid #eee;"><img id="prev_icon" src="" alt=""></div>
                    </div>
                    
                    <div class="full-width">
                        <label><?= $L['lbl_layout'] ?> <span style="font-weight:normal; color:#999;"><?= $L['lbl_layout_hint'] ?></span></label>
                        <div style="display:flex; gap:10px;">
                            <input type="text" name="page_layout" id="inp_layout" placeholder="z.B. default_3x3" style="flex:1;">
                            <select onchange="$('#inp_layout').val(this.value)" style="flex:1; cursor:pointer; background:#fff;">
                                <option value="" disabled selected>⚡ ...</option>
                                <option value="default_3x3">default_3x3</option>
                                <option value="frontpage">frontpage</option>
                                <option value="info_loyalty">info_loyalty</option>
                                <option value="info_duckets">info_duckets</option>
                                <option value="club_buy">club_buy</option>
                                <option value="pets">pets</option>
                                <option value="pets2">pets2</option>
                                <option value="bots">bots</option>
                                <option value="trophies">trophies</option>
                                <option value="spaces_new">spaces_new</option>
                                <option value="badge_display">badge_display</option>
                                <option value="marketplace">marketplace</option>
                                <option value="sold_ltd_items">sold_ltd_items</option>
                                <option value="vip_buy">vip_buy</option>
                                <option value="play_snowstorm">play_snowstorm</option>
                            </select>
                        </div>
                    </div>

                    <div><label><?= $L['lbl_min_rank'] ?></label><input type="number" name="min_rank" id="inp_rank" value="1"></div>

                    <div class="full-width">
                        <label><?= $L['lbl_headline'] ?> <a href="#" onclick="openImageFolder()" style="float:right; font-size:9px;"><?= $L['link_folder'] ?></a></label>
                        <input type="text" name="page_headline" id="inp_headline" onkeyup="updateHeaderPreview()">
                        <div class="image-preview"><img id="prev_header" src="" alt="Vorschau"></div>
                    </div>
                    <div class="full-width">
                        <label><?= $L['lbl_teaser'] ?></label><input type="text" name="page_teaser" id="inp_teaser" onkeyup="updateTeaserPreview()">
                        <div class="image-preview"><img id="prev_teaser" src="" alt="Vorschau"></div>
                    </div>

                    <div class="full-width"><label><?= $L['lbl_text1'] ?></label><textarea name="page_text1" id="inp_text1" rows="3"></textarea></div>
                    <div class="full-width"><label><?= $L['lbl_text2'] ?></label><textarea name="page_text2" id="inp_text2" rows="3"></textarea></div>
                    <div class="full-width"><label><?= $L['lbl_details'] ?></label><textarea name="page_text_details" id="inp_text_details" rows="3"></textarea></div>

                    <div style="display:flex; gap:20px;">
                        <label style="display:flex; align-items:center; gap:5px;"><input type="checkbox" name="enabled" id="inp_enabled" value="1" style="width:auto;"> <?= $L['lbl_enabled'] ?></label>
                        <label style="display:flex; align-items:center; gap:5px;"><input type="checkbox" name="visible" id="inp_visible" value="1" style="width:auto;"> <?= $L['lbl_visible'] ?></label>
                    </div>
                </div>

                <button type="button" class="btn-save" onclick="saveData()"><?= $L['btn_save'] ?></button>
                <div style="text-align:center; margin-top:15px;">
                    <a href="#" onclick="deletePage()" style="color:#c0392b; font-size:11px; text-decoration:none;"><?= $L['link_delete'] ?></a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Config und Sprache an JS übergeben
    var currentTab = <?= $activeTabId ?>;
    var assetUrl = "<?= $url_images ?>"; 
    var iconUrl  = "<?= $url_icons ?>"; 
    
    // Übersetzung für JS
    var lang = {
        move_confirm: "<?= $L['js_move_confirm'] ?>",
        delete_confirm: "<?= $L['js_delete_confirm'] ?>",
        saved: "<?= $L['btn_saved'] ?>",
        save: "<?= $L['btn_save'] ?>",
        btn_sort: "<?= $L['btn_sort'] ?>",
        btn_done: "<?= $L['btn_done'] ?>"
    };
    
    var isDragMode = false;

    $(document).ready(function() {
        if(currentTab > 0) loadSidebar(currentTab);
        else $('#loading-spinner').html('Keine Tabs gefunden.');

        var tabContainer = document.getElementById('tabs-sortable-container');
        Sortable.create(tabContainer, {
            animation: 150,
            onEnd: function (evt) {
                var orderedIds = [];
                $('#tabs-sortable-container .tab-item').each(function() { orderedIds.push($(this).data('id')); });
                $.post('api.php', { action: 'sort_tabs', list: orderedIds });
            }
        });
        
        $('.tab-drop-target').on('mouseup', function() {
            var draggedItem = $('.dd-dragel');
            if(draggedItem.length > 0) {
                var itemId = draggedItem.find('.dd-item').data('id');
                var targetTabId = $(this).data('id');
                var targetName = $(this).data('name');
                if(confirm(lang.move_confirm + "'" + targetName + "'?")) {
                    $.post('api.php', { action: 'move_to_tab', id: itemId, new_parent: targetTabId }, function(res) {
                        if(res.status == 'success') location.reload(); else alert("Fehler: " + res.msg);
                    }, 'json');
                }
            }
        });
        $('.tab-drop-target').on('mouseenter', function() { if($('.dd-dragel').length > 0) $(this).addClass('drag-hover'); })
                             .on('mouseleave', function() { $(this).removeClass('drag-hover'); });
    });

    window.toggleSortMode = function() {
        isDragMode = !isDragMode;
        var btn = $('#sortToggleBtn');
        if(isDragMode) {
            btn.addClass('active').html('<i class="fas fa-check"></i> ' + lang.btn_done);
            $('body').addClass('drag-mode-active');
            loadSidebar(currentTab); 
        } else {
            btn.removeClass('active').html('<i class="fas fa-arrows-alt"></i> ' + lang.btn_sort);
            $('body').removeClass('drag-mode-active');
            try { $('#nestable-sidebar').nestable('destroy'); } catch(e) {}
            loadSidebar(currentTab);
        }
    }

    function loadSidebar(parentId) {
        $('#nestable-sidebar').empty().off(); 
        $('#loading-spinner').show();
        $.post('api.php', { action: 'get_tree', parent_id: parentId }, function(data) {
            $('#loading-spinner').hide();
            $('#nestable-sidebar').html(data);
            if(isDragMode) {
                $('#nestable-sidebar').nestable({ maxDepth: 10, handleClass: 'dd-handle-custom' }).on('change', function() {
                    var json = $('.dd').nestable('serialize');
                    $.post('api.php', { action: 'sort', tree: JSON.stringify(json), parent_id: currentTab });
                });
                $('#nestable-sidebar').nestable('expandAll');
                $('.dd-list').show(); 
                $('.arrow-box i').addClass('open');
            } 
        });
    }
    
    window.editCurrentTab = function() { loadPage(currentTab); }
    window.toggleFolder = function(arrow, event) {
        if(isDragMode) return; 
        event.stopPropagation();
        var li = $(arrow).closest('li');
        var childList = li.children('ol');
        if(childList.length > 0) { childList.slideToggle(150); $(arrow).toggleClass('open'); }
    }

    window.loadPage = function(id) {
        if(isDragMode) return; 
        $('.dd-item').removeClass('active-row'); $('*[data-id="'+id+'"]').addClass('active-row');
        $('#welcome-msg').hide(); $('#editForm').hide();
        
        $.ajax({ url: 'api.php', type: 'POST', data: { action: 'get_page', id: id }, dataType: 'json', success: function(data) {
                if(data.error) { alert(data.error); return; }
                $('#editForm').fadeIn(200); $('#display-id').text('(ID: ' + data.id + ')');
                $('#inp_id').val(data.id); $('#inp_caption').val(data.caption); $('#inp_order').val(data.order_num); $('#inp_parent').val(data.parent_id);
                $('#inp_icon').val(data.icon_image); $('#inp_layout').val(data.page_layout); $('#inp_rank').val(data.min_rank);
                $('#inp_headline').val(data.page_headline); $('#inp_teaser').val(data.page_teaser);
                $('#inp_text1').val(data.page_text1); $('#inp_text2').val(data.page_text2); $('#inp_text_details').val(data.page_text_details);
                $('#inp_enabled').prop('checked', data.enabled == 1); $('#inp_visible').prop('checked', data.visible == 1);
                var count = data.item_count || 0; $('#item-count-badge').text(count + ' ' + "<?= $L['items'] ?>").css('background', count > 0 ? '#27ae60' : '#bdc3c7');
                updateHeaderPreview(); updateTeaserPreview(); updateIconPreview();
            }, error: function() { alert("Fehler beim Laden."); } });
    }
    window.updateHeaderPreview = function() { var val = $('#inp_headline').val(); if(val) $('#prev_header').attr('src', assetUrl + val + '.gif').show(); else $('#prev_header').hide(); }
    window.updateTeaserPreview = function() { var val = $('#inp_teaser').val(); if(val) $('#prev_teaser').attr('src', assetUrl + val + '.gif').show(); else $('#prev_teaser').hide(); }
    window.updateIconPreview = function() { var val = $('#inp_icon').val(); if(val > 0) $('#prev_icon').attr('src', iconUrl + val + '.png').show(); else $('#prev_icon').hide(); }
    window.openImageFolder = function() { window.open(assetUrl, '_blank'); }
    window.saveData = function() { var formData = $('#editForm').serialize(); $.post('api.php', formData, function(res) { if(res.status == 'success') { var btn = $('.btn-save'); btn.css('background', '#2ecc71').text(lang.saved); setTimeout(function(){ btn.css('background', '#27AE60').text(lang.save); if($('#inp_id').val() == currentTab) location.reload(); else loadSidebar(currentTab); }, 800); } else { alert("Fehler: " + res.msg); } }, 'json'); }
    window.createSubPage = function(pid) { var n = prompt("Name:"); if(n) $.post('api.php', {action:'create_child', parent_id:pid, caption:n}, function(){ loadSidebar(pid); }); }
    window.createNewRoot = function() { var n = prompt("Name:"); if(n) $.post('api.php', {action:'create_root', caption:n}, function(){ location.reload(); }); }
    window.deletePage = function() { if(confirm(lang.delete_confirm)) { $.post('api.php', { action: 'delete', id: $('#inp_id').val() }, function() { location.reload(); }); } }
    function filterSidebar() { var value = $('#searchInput').val().toLowerCase(); $("#nestable-sidebar .dd-item").filter(function() { var text = $(this).find('.folder-name').text().toLowerCase(); $(this).toggle(text.indexOf(value) > -1); }); }
</script>

</body>
</html>