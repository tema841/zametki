// create.php — создание заметки
<?php
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $body = trim($_POST['body'] ?? '');
    $tagsInput = trim($_POST['tags'] ?? '');
    $isPinned = isset($_POST['is_pinned']) ? 1 : 0;

    try {
        $pdo->beginTransaction();

        // Создаём заметку
        $stmt = $pdo->prepare(
            'INSERT INTO notes (user_id, title, body, is_pinned) VALUES (?, ?, ?, ?)'
        );
        $stmt->execute([$currentUserId, $title, $body, $isPinned]);
        $noteId = (int)$pdo->lastInsertId();

        // Обрабатываем теги (разделяем по запятой)
        if ($tagsInput !== '') {
            $tagNames = array_map('trim', explode(',', $tagsInput));
            foreach ($tagNames as $tagName) {
                if ($tagName === '') continue;
                
                // Ищем или создаём тег для пользователя
                $stmt = $pdo->prepare(
                    'INSERT INTO tags (name, user_id) VALUES (?, ?)
                     ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id)'
                );
                $stmt->execute([$tagName, $currentUserId]);
                $tagId = (int)$pdo->lastInsertId();

                // Если тег уже существовал, получаем его id
                if ($tagId === 0) {
                    $stmt = $pdo->prepare('SELECT id FROM tags WHERE name = ? AND user_id = ?');
                    $stmt->execute([$tagName, $currentUserId]);
                    $tagId = $stmt->fetchColumn();
                }

                // Связываем тег с заметкой
                $stmt = $pdo->prepare('INSERT IGNORE INTO note_tags (note_id, tag_id) VALUES (?, ?)');
                $stmt->execute([$noteId, $tagId]);
            }
        }

        $pdo->commit();
        header('Location: index.php');
        exit;
    } catch (PDOException $e) {
        $pdo->rollBack();
        die('Ошибка: ' . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Новая заметка</title>
    <style>
        body { font-family: Arial; max-width: 600px; margin: 20px auto; }
        label { display: block; margin: 15px 0 5px; font-weight: bold; }
        input[type="text"], textarea { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; }
        textarea { min-height: 150px; resize: vertical; }
        button { background: #1976d2; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; margin-right: 10px; }
        .cancel { background: #9e9e9e; text-decoration: none; padding: 10px 20px; border-radius: 5px; color: white; display: inline-block; }
        .hint { color: #666; font-size: 14px; margin-top: 5px; }
    </style>
</head>
<body>
    <h1>📌 Новая заметка</h1>
    <form method="post">
        <label>Заголовок:</label>
        <input type="text" name="title" placeholder="Введите заголовок">

        <label>Текст заметки:</label>
        <textarea name="body" placeholder="Пишите здесь..."></textarea>

        <label>Теги:</label>
        <input type="text" name="tags" placeholder="работа, идеи, важное">
        <div class="hint">Введите теги через запятую</div>

        <label>
            <input type="checkbox" name="is_pinned" value="1"> 📌 Закрепить заметку
        </label>

        <div style="margin-top: 20px;">
            <button type="submit">💾 Сохранить</button>
            <a href="index.php" class="cancel">Отмена</a>
        </div>
    </form>
</body>
</html>