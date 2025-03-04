<?php
include 'db.php';

$search = isset($_GET['search']) ? trim($_GET['search']) : "";
$query = "SELECT * FROM chat";
if (!empty($search)) {
    $query .= " WHERE msg LIKE '%$search%'";
}
$query .= " ORDER BY pinned DESC, date DESC";

$messages = $con->query($query);
?>

<table class="chat-table">
    <tr>
        <th>Username</th>
        <th>Message</th>
        <th>Time</th>
        <th>Actions</th>
    </tr>
    <?php while ($row = $messages->fetch_assoc()) : ?>
        <tr <?= $row['pinned'] ? "class='pinned'" : "" ?>>
            <td><?= htmlspecialchars($row['name']); ?></td>
            <td><?= nl2br(htmlspecialchars($row['msg'])); ?></td>
            <td><?= date('d-m-y h:i A', strtotime($row['date'])); ?></td>
            <td>
                <button class="edit-btn" onclick="parent.editMessage(<?= $row['id']; ?>, `<?= addslashes($row['msg']); ?>`)">Edit</button>
                <button class="delete-btn" onclick="parent.confirmDelete(<?= $row['id']; ?>)">Delete</button>
                <button class="pin-btn" onclick="parent.pinMessage(<?= $row['id']; ?>)"><?= $row['pinned'] ? "Unpin" : "Pin"; ?></button>
            </td>
        </tr>
    <?php endwhile; ?>
</table>
