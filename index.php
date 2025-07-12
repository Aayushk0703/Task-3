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

$user_id = $_SESSION['user_id'];
$limit = 5;
$page = isset($_GET['page']) ? $_GET['page'] : 1;
$offset = ($page - 1) * $limit;
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
?>
<!DOCTYPE html>
<html>
<head>
    <title>Dashboard - Blog</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(to right, #ffecd2, #fcb69f);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
        }

        .container {
            max-width: 900px;
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

        .navbar {
            border-radius: 8px;
        }

        .notification-box {
            min-height: 60px;
        }

        .card {
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }

        .card-footer .btn {
            border-radius: 6px;
        }

        .pagination .page-link {
            border-radius: 6px;
        }
        .classy-logout {
    background: linear-gradient(to right,rgb(88, 73, 203),rgb(241, 151, 124)); /* peachy coral gradient */
    color: white;
    border: none;
    border-radius: 8px;
    padding: 8px 16px;
    font-weight: 500;
    transition: background 0.3s ease; 
}

.classy-logout:hover {
    background: linear-gradient(to right,rgb(88, 73,203),rgb(241, 151, 124));
}
.classy-search{
    background: linear-gradient(to right,rgb(196, 137, 185),rgb(241, 151, 124)); /* peachy coral gradient */
    color: white;
    border: none;
    border-radius: 8px;
    padding: 8px 16px;
    font-weight: 500;
    transition: background 0.3s ease; 
}

    </style>
</head>
<body>
<div class="container">

    <!-- 🔝 Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4 px-3">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold" href="#">📝 Blog Dashboard</a>
            <div class="d-flex">
                <a href="add_post.php" class="btn btn-outline-light me-2">➕ Add Post</a>
                <a href="logout.php" class="btn classy-logout">🚪 Logout</a>
            </div>
        </div>
    </nav>

    <!-- ✅ Success Message -->
    <div class="notification-box" id="messageBox">
        <?php if (isset($_GET['deleted']) && $_GET['deleted'] == 1): ?>
            <div class="alert alert-success fw-bold" id="message">
                ✅ Post deleted successfully!
            </div>
        <?php endif; ?>
    </div>

    <!-- 👋 Welcome -->
    <h2 class="text-dark mb-1">Welcome, <?= htmlspecialchars($_SESSION['user']) ?>!</h2>
    <p class="text-muted mb-4">Here you can manage your blog posts.</p>

    <!-- 🔎 Search Form -->
    <form method="GET" action="" class="d-flex mb-4">
        <input type="text" name="search" class="form-control me-2" placeholder="Search posts..."
               value="<?= htmlspecialchars($search) ?>">
        <button type="submit" class="btn classy-search">🔍 Search</button>
    </form>

    <!-- 📄 Posts Display -->
    <?php
    $sql = "SELECT * FROM posts WHERE user_id = $user_id";
    if (!empty($search)) {
        $sql .= " AND (title LIKE '%$search%' OR content LIKE '%$search%')";
    }
    $sql .= " ORDER BY created_at DESC LIMIT $offset, $limit";

    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo "<div class='card mb-4'>";
            echo "<div class='card-body'>";
            echo "<h5 class='card-title'>" . htmlspecialchars($row['title']) . "</h5>";
            echo "<p class='card-text'>" . nl2br(htmlspecialchars($row['content'])) . "</p>";
            echo "<small class='text-muted'>🗓️ Posted on: " . $row['created_at'] . "</small>";
            echo "</div>";
            echo "<div class='card-footer text-end'>";
            echo "<a href='edit_post.php?id=" . $row['id'] . "' class='btn btn-sm btn-outline-primary me-2'>✏️ Edit</a>";
            echo "<a href='delete_post.php?id=" . $row['id'] . "' class='btn btn-sm btn-outline-danger' onclick='return confirm(\"Are you sure?\")'>🗑️ Delete</a>";
            echo "</div>";
            echo "</div>";
        }
    } else {
        echo "<div class='alert alert-warning fw-bold'>😕 No posts found";
        if (!empty($search)) {
            echo " matching '<strong>" . htmlspecialchars($search) . "</strong>'";
        }
        echo ".</div>";
    }

    // 🔄 Pagination
    $totalQuery = "SELECT COUNT(*) AS total FROM posts WHERE user_id = $user_id";
    if (!empty($search)) {
        $totalQuery .= " AND (title LIKE '%$search%' OR content LIKE '%$search%')";
    }
    $totalResult = $conn->query($totalQuery);
    $totalRow = $totalResult->fetch_assoc();
    $totalPosts = $totalRow['total'];
    $totalPages = ceil($totalPosts / $limit);

    if ($totalPages > 1) {
        echo "<nav><ul class='pagination justify-content-center'>";
        for ($i = 1; $i <= $totalPages; $i++) {
            $active = $i == $page ? "active" : "";
            echo "<li class='page-item $active'><a class='page-link' href='?search=" . urlencode($search) . "&page=$i'>$i</a></li>";
        }
        echo "</ul></nav>";
    }

    $conn->close();
    ?>

</div>

<!-- ✅ Auto-hide message -->
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
