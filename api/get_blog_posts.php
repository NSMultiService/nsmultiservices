<?php
header('Content-Type: application/json');
require_once '../config/db.php';

$category = isset($_GET['category']) ? $_GET['category'] : 'all';

$sql = "SELECT * FROM blog_posts ORDER BY date DESC";

if ($category !== 'all') {
    $category = $conn->real_escape_string($category);
    $sql = "SELECT * FROM blog_posts WHERE category = '$category' ORDER BY date DESC";
}

$result = $conn->query($sql);
$posts = [];

while ($row = $result->fetch_assoc()) {
    $posts[] = $row;
}

echo json_encode($posts);
$conn->close();
?>