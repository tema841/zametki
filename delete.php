// delete.php — удаление заметки
<?php
require 'db.php';

$id = (int)($_GET['id'] ?? 0);

if ($id > 0) {
    $stmt = $pdo->prepare('DELETE FROM notes WHERE id = ? AND user_id = ?');
    $stmt->execute([$id, $currentUserId]);
}

header('Location: index.php');
exit;