<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "blog", 3307);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$post_id = $_GET['id'] ?? null;
$user_id = $_SESSION['user_id'];
$success = false;
$error = "";

if ($post_id) {
    $stmt = $conn->prepare("SELECT * FROM posts WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $post_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $post = $result->fetch_assoc();
    $stmt->close();

    if (!$post) {
        die("❌ Post not found or unauthorized access.");
    }
} else {
    die("❌ Invalid post ID.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);

    if (!empty($title) && !empty($content)) {
        $stmt = $conn->prepare("UPDATE posts SET title = ?, content = ? WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ssii", $title, $content, $post_id, $user_id);
        $stmt->execute();
        $stmt->close();
        $success = true;
    } else {
        $error = "❌ All fields are required!";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Edit Post</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(to right, #ffecd2, #fcb69f);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
        }

        .container {
            max-width: 700px;
            margin-top: 40px;
            margin-bottom: 60px;
            background-color: rgba(255, 255, 255, 0.95);
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 8px 16px rgba(0,0,0,0.2);
            animation: fadeIn 0.6s ease-in-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .notification-box {
            min-height: 60px;
        }
    </style>
</head>
<body>
<div class="container">
    <h2 class="mb-4 text-center">✏️ Edit Post</h2>

    <div class="notification-box" id="messageBox">
        <?php if ($success): ?>
            <div class="alert alert-success fw-bold" id="message">
                ✅ Post updated successfully!
            </div>
        <?php elseif (!empty($error)): ?>
            <div class="alert alert-danger fw-bold" id="message">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>
    </div>

    <form method="POST">
        <div class="mb-3">
            <label class="form-label fw-semibold">Title</label>
            <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($post['title']) ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label fw-semibold">Content</label>
            <textarea name="content" class="form-control" rows="6" required><?= htmlspecialchars($post['content']) ?></textarea>
        </div>
        <div class="d-flex justify-content-between mt-3">
            <button type="submit" class="btn btn-primary">💾 Update Post</button>
            <a href="index.php" class="btn btn-outline-dark">🏠 Go to Dashboard</a>
        </div>
    </form>
</div>

<script>
  setTimeout(function() {
    const msg = document.getElementById("message");
    if (msg) {
      msg.style.transition = "opacity 0.5s ease";
      msg.style.opacity = "0";
      setTimeout(() => msg.remove(), 500);
    }
  }, 2000);
</script>
</body>
</html>
