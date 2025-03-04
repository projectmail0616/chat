<?php
session_start();
include 'db.php';

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'];
$search = isset($_GET['search']) ? trim($_GET['search']) : "";

// Handle sending and updating messages
if (isset($_POST['submit'])) {
    $msg = nl2br(htmlspecialchars(trim($_POST['msg'])));

    if (!empty($msg)) {
        if (!empty($_POST['edit_id'])) {
            $edit_id = $_POST['edit_id'];
            $query = "UPDATE chat SET msg='$msg' WHERE id='$edit_id' AND name='$username'";
        } else {
            $query = "INSERT INTO chat (name, msg, date) VALUES ('$username', '$msg', NOW())";
        }
        $con->query($query);
    }
    header("Location: home.php");
    exit();
}

// Handle message deletion
if (isset($_POST['delete_id'])) {
    $delete_id = $_POST['delete_id'];
    $query = "DELETE FROM chat WHERE id='$delete_id' AND name='$username'";
    $con->query($query);
    exit();
}

// Handle pin/unpin messages
if (isset($_POST['pin_id'])) {
    $pin_id = $_POST['pin_id'];
    $query = "UPDATE chat SET pinned = NOT pinned WHERE id='$pin_id'";
    $con->query($query);
    exit();
}

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    unset($_SESSION['username']);
    header("Location: login.php");
    exit();
}

// Load chat messages with search functionality
$query = "SELECT * FROM chat";
if (!empty($search)) {
    $query .= " WHERE msg LIKE '%$search%'";
}
$query .= " ORDER BY pinned DESC, date DESC";

$messages = $con->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat Application</title>
    <link rel="stylesheet" href="style.css">
    <script>
        function confirmDelete(id) {
            if (confirm("Are you sure you want to delete this message?")) {
                var xhr = new XMLHttpRequest();
                xhr.open("POST", "home.php", true);
                xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
                xhr.send("delete_id=" + id);
                setTimeout(() => location.reload(), 500);
            }
        }

        function editMessage(id, message) {
            document.getElementById("edit_id").value = id;
            document.getElementById("msg").value = message.replace(/<br\s*\/?>/g, "\n");
            document.getElementById("submit_btn").value = "Update";
        }

        function pinMessage(id) {
            var xhr = new XMLHttpRequest();
            xhr.open("POST", "home.php", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            xhr.send("pin_id=" + id);
            setTimeout(() => location.reload(), 500);
        }

        function handleKeyPress(event) {
            if (event.key === "Enter" && !event.shiftKey) {
                event.preventDefault();
                document.getElementById("submit_btn").click();
            }
        }

        function searchMessages() {
            let searchValue = document.getElementById("search").value;
            window.location.href = "home.php?search=" + encodeURIComponent(searchValue);
        }

        function highlightText(text, search) {
            if (!search) return text;
            let regex = new RegExp(`(${search})`, "gi");
            return text.replace(regex, "<span class='highlight'>$1</span>");
        }

        window.onload = function () {
            let searchField = document.getElementById("search");
            searchField.focus();  // Set cursor in the search field after reloading
            searchField.setSelectionRange(searchField.value.length, searchField.value.length); 
        };
    </script>
    <style>
        .highlight { background-color: yellow; font-weight: bold; }
        .pinned { background-color: #f8e71c !important; }
        .error-msg { color: red; text-align: center; font-size: 16px; font-weight: bold; margin-top: 10px; }
    </style>
</head>
<body>

<div id="container">
    <header>
        <h2>Chat Application</h2>
        <div class="user-info">
            <span>Welcome, <strong><?php echo $username; ?></strong></span>
            <a href="home.php?logout='1'" class="logout">Logout</a>
        </div>
    </header>

    <div id="search-box">
        <input type="text" id="search" placeholder="Search messages..." value="<?= htmlspecialchars($search) ?>" onkeyup="searchMessages()">
    </div>

    <div id="chat-box">
        <table class="chat-table">
            <tr>
                <th>Username</th>
                <th>Message</th>
                <th>Time</th>
                <th>Actions</th>
            </tr>
            <?php if ($messages->num_rows > 0) : ?>
                <?php while ($row = $messages->fetch_assoc()) : ?>
                    <?php $highlightedMsg = !empty($search) ? preg_replace("/($search)/i", "<span class='highlight'>$1</span>", $row['msg']) : $row['msg']; ?>
                    <tr<?= $row['pinned'] ? " class='pinned'" : "" ?>>
                        <td><?= $row['name']; ?></td>
                        <td><?= $highlightedMsg; ?></td>
                        <td><?= date('d-m-y h:i A', strtotime($row['date'])); ?></td>
                        <td>
                            <button class="edit-btn" onclick="editMessage(<?= $row['id']; ?>, `<?= htmlspecialchars($row['msg']); ?>`)">Edit</button>
                            <button class="delete-btn" onclick="confirmDelete(<?= $row['id']; ?>)">Delete</button>
                            <button class="pin-btn" onclick="pinMessage(<?= $row['id']; ?>)"><?= $row['pinned'] ? "Unpin" : "Pin"; ?></button>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else : ?>
                <tr>
                    <td colspan="4" class="error-msg">No messages found!</td>
                </tr>
            <?php endif; ?>
        </table>
    </div>

    <form method="post" action="home.php">
        <input type="hidden" name="edit_id" id="edit_id">
        <textarea name="msg" id="msg" placeholder="Enter message..." maxlength="250" onkeydown="handleKeyPress(event)"></textarea>
        <button type="submit" name="submit" id="submit_btn">Send</button>
    </form>
</div>

</body>
</html>
