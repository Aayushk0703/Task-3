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

$success = false;
$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    $user_id = $_SESSION['user_id'];

    if (!empty($title) && !empty($content)) {
        $stmt = $conn->prepare("INSERT INTO posts (title, content, user_id, created_at) VALUES (?, ?, ?, NOW())");
        $stmt->bind_param("ssi", $title, $content, $user_id);
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
    <title>Add Post</title>
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
    <h2 class="mb-4 text-center">➕ Add New Post</h2>

    <div class="notification-box" id="messageBox">
        <?php if ($success): ?>
            <div class="alert alert-success fw-bold" id="message">
                ✅ Post added successfully!
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
            <input type="text" name="title" class="form-control" placeholder="Enter post title" required>
        </div>
        <div class="mb-3">
            <label class="form-label fw-semibold">Content</label>
            <textarea name="content" class="form-control" rows="6" placeholder="Write your post here..." required></textarea>
        </div>
        <div class="d-flex justify-content-between mt-3">
            <button type="submit" class="btn btn-success">📝 Submit Post</button>
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
  }, 3000);
</script>
</body>
</html>
