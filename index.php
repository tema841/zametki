// index.php — список заметок
<?php
require 'db.php';

// Получаем заметки с тегами
$stmt = $pdo->prepare(
    'SELECT n.*, GROUP_CONCAT(t.name ORDER BY t.name SEPARATOR ", ") AS tags
     FROM notes n
     LEFT JOIN note_tags nt ON nt.note_id = n.id
     LEFT JOIN tags t ON t.id = nt.tag_id
     WHERE n.user_id = ?
     GROUP BY n.id
     ORDER BY n.is_pinned DESC, n.updated_at DESC'
);
$stmt->execute([$currentUserId]);
$notes = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Мои заметки</title>
    <style>
        body { font-family: Arial; max-width: 800px; margin: 20px auto; }
        .note { border: 1px solid #ddd; margin: 10px 0; padding: 15px; border-radius: 5px; }
        .pinned { background: #fff9c4; border-left: 4px solid #f9a825; }
        .note h3 { margin: 0 0 10px; }
        .tags { color: #666; font-size: 14px; margin: 10px 0; }
        .note-actions a { margin-right: 10px; color: #1976d2; text-decoration: none; }
        .note-actions a.delete { color: #d32f2f; }
        .add-btn { display: inline-block; background: #1976d2; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin-bottom: 20px; }
        .empty { color: #999; text-align: center; padding: 50px; }
    </style>
</head>
<body>
    <h1>📝 Мои заметки</h1>
    <a href="create.php" class="add-btn">+ Новая заметка</a>

    <?php if (empty($notes)): ?>
        <div class="empty">У вас пока нет заметок. Создайте первую!</div>
    <?php else: ?>
        <?php foreach ($notes as $note): ?>
            <div class="note <?= $note['is_pinned'] ? 'pinned' : '' ?>">
                <h3><?= htmlspecialchars($note['title'] ?: 'Без названия') ?></h3>
                <?php if ($note['body']): ?>
                    <p><?= nl2br(htmlspecialchars($note['body'])) ?></p>
                <?php endif; ?>
                <?php if ($note['tags']): ?>
                    <div class="tags">🏷️ <?= htmlspecialchars($note['tags']) ?></div>
                <?php endif; ?>
                <div class="note-actions">
                    <a href="delete.php?id=<?= $note['id'] ?>" class="delete" onclick="return confirm('Удалить заметку?')">🗑️ Удалить</a>
                </div>
                <small style="color:#999;">Обновлено: <?= $note['updated_at'] ?></small>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>