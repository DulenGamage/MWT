<!-- app_core/modules/notes/views/index.php -->
<?php
require_once __DIR__ . '/../controllers/NotesController.php';
$notesController = new NotesController();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_note'])) {
    $notesController->saveNote($_POST);
    header("Location: ?module=notes");
    exit;
}

if (isset($_GET['delete'])) {
    $notesController->deleteNote($_GET['delete']);
    header("Location: ?module=notes");
    exit;
}

$editNote = null;
if (isset($_GET['edit'])) {
    $editNote = $notesController->getNoteById($_GET['edit']);
}

$allNotes = $notesController->getAllNotes();
?>

<div style="font-family: Arial, sans-serif; margin-top: 20px;">
    
    <!-- Top Bar with Title and Toggle Add Button -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2 style="margin: 0;">Daily Operational Notes</h2>
        <button onclick="toggleNoteForm()" style="padding: 10px 18px; background: #6f42c1; color: white; border: none; cursor: pointer; border-radius: 4px; font-weight: bold; font-size: 14px;">
            + Add New Note
        </button>
    </div>

    <!-- Hidden/Collapsible Add / Edit Note Form Box -->
    <div id="noteModal" style="display: <?= $editNote ? 'block' : 'none' ?>; background: #f9f9f9; padding: 20px; border-radius: 8px; margin-bottom: 30px; border: 1px solid #ddd; box-shadow: 0 4px 8px rgba(0,0,0,0.1);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
            <h3 style="margin:0;"><?= $editNote ? 'Edit Note' : 'Create Operational Note' ?></h3>
            <button onclick="toggleNoteForm()" style="background: none; border: none; font-size: 18px; cursor: pointer; color: #666;">&times;</button>
        </div>

        <form action="?module=notes" method="POST" style="display: grid; grid-template-columns: 2fr 1fr 1fr 1fr; gap: 15px;">
            <?php if ($editNote): ?>
                <input type="hidden" name="note_id" value="<?= $editNote['id'] ?>">
            <?php endif; ?>

            <input type="text" name="title" value="<?= $editNote ? htmlspecialchars($editNote['title']) : '' ?>" placeholder="Note Subject / Title" required style="padding: 10px; border: 1px solid #ccc; border-radius: 4px;">

            <input type="text" name="category" value="<?= $editNote ? htmlspecialchars($editNote['category']) : '' ?>" placeholder="Category (e.g. Orders, Purchasing)" style="padding: 10px; border: 1px solid #ccc; border-radius: 4px;">

            <select name="priority" style="padding: 10px; border: 1px solid #ccc; border-radius: 4px;">
                <option value="Low" <?= ($editNote && $editNote['priority'] === 'Low') ? 'selected' : '' ?>>Low Priority</option>
                <option value="Medium" <?= (!$editNote || $editNote['priority'] === 'Medium') ? 'selected' : '' ?>>Medium Priority</option>
                <option value="High" <?= ($editNote && $editNote['priority'] === 'High') ? 'selected' : '' ?>>High Priority</option>
            </select>

            <select name="status" style="padding: 10px; border: 1px solid #ccc; border-radius: 4px;">
                <option value="Pending" <?= (!$editNote || $editNote['status'] === 'Pending') ? 'selected' : '' ?>>Pending</option>
                <option value="In Progress" <?= ($editNote && $editNote['status'] === 'In Progress') ? 'selected' : '' ?>>In Progress</option>
                <option value="Completed" <?= ($editNote && $editNote['status'] === 'Completed') ? 'selected' : '' ?>>Completed</option>
            </select>

            <textarea name="content" placeholder="Detailed notes, task steps, or supplier/customer logs..." required style="grid-column: span 4; height: 80px; padding: 10px; border: 1px solid #ccc; border-radius: 4px;"></textarea>

            <button type="submit" name="save_note" style="grid-column: span 4; padding: 12px; background: #6f42c1; color: white; border: none; cursor: pointer; border-radius: 4px; font-weight: bold; font-size: 15px;">Save Note</button>
        </form>
    </div>

    <!-- Notes Data Table -->
    <table border="1" cellpadding="10" cellspacing="0" style="width: 100%; border-collapse: collapse; background: white;">
        <thead>
            <tr style="background: #f1f3f5; text-align: left;">
                <th>Title & Content</th>
                <th>Category</th>
                <th>Priority</th>
                <th>Status</th>
                <th>Date / Time</th>
                <th style="text-align: center; width: 90px;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($allNotes)): ?>
                <?php foreach ($allNotes as $note): ?>
                    <tr>
                        <td>
                            <strong style="font-size: 15px; color: #222;"><?= htmlspecialchars($note['title']) ?></strong>
                            <p style="margin: 5px 0 0 0; font-size: 13px; color: #555; white-space: pre-line;"><?= htmlspecialchars($note['content']) ?></p>
                        </td>
                        <td><span style="background: #e2e8f0; padding: 3px 8px; border-radius: 4px; font-size: 12px; font-weight: bold;"><?= htmlspecialchars($note['category']) ?></span></td>
                        <td>
                            <?php if ($note['priority'] === 'High'): ?>
                                <span style="background: #f8d7da; color: #721c24; padding: 3px 8px; border-radius: 4px; font-size: 11px; font-weight: bold;">HIGH</span>
                            <?php elseif ($note['priority'] === 'Medium'): ?>
                                <span style="background: #fff3cd; color: #856404; padding: 3px 8px; border-radius: 4px; font-size: 11px; font-weight: bold;">MEDIUM</span>
                            <?php else: ?>
                                <span style="background: #d1ecf1; color: #0c5460; padding: 3px 8px; border-radius: 4px; font-size: 11px; font-weight: bold;">LOW</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($note['status'] === 'Completed'): ?>
                                <span style="background: #d4edda; color: #155724; padding: 3px 8px; border-radius: 4px; font-size: 11px; font-weight: bold;">COMPLETED</span>
                            <?php else: ?>
                                <span style="background: #e2e8f0; color: #333; padding: 3px 8px; border-radius: 4px; font-size: 11px; font-weight: bold;"><?= strtoupper($note['status']) ?></span>
                            <?php endif; ?>
                        </td>
                        <td><small><?= htmlspecialchars($note['created_at']) ?></small></td>
                        <td style="text-align: center;">
                            <a href="?module=notes&edit=<?= $note['id'] ?>" title="Edit Note" style="text-decoration: none; padding: 4px 6px; background: #ffc107; color: #fff; border-radius: 4px; font-size: 13px;">✏️</a>
                            <a href="?module=notes&delete=<?= $note['id'] ?>" onclick="return confirm('Delete this note?')" title="Delete Note" style="text-decoration: none; padding: 4px 6px; background: #dc3545; color: #fff; border-radius: 4px; font-size: 13px; margin-left: 3px;">🗑️</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="6" style="text-align:center; color: #777; padding: 20px;">No operational notes logged yet. Click "+ Add New Note" above to create one.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<script>
function toggleNoteForm() {
    var modal = document.getElementById('noteModal');
    if (modal.style.display === 'none' || modal.style.display === '') {
        modal.style.display = 'block';
    } else {
        modal.style.display = 'none';
    }
}
</script>