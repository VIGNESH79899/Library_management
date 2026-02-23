<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: ../auth/login.php");
    exit;
}
include "../config/db.php";

if (!isset($_GET['id'])) {
    header("Location: books.php");
    exit;
}

$id = $_GET['id'];
$book = $conn->query("SELECT * FROM book WHERE Book_ID=$id")->fetch_assoc();

if (!$book) {
    header("Location: books.php");
    exit;
}

$categories = $conn->query("SELECT * FROM category");
$publishers = $conn->query("SELECT * FROM publisher");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $author = $_POST['author'];
    $isbn = $_POST['isbn'];
    $category = $_POST['category'];
    $publisher = $_POST['publisher'];

    $stmt = $conn->prepare("
        UPDATE book 
        SET Title=?, Author=?, ISBN=?, Category_ID=?, Publisher_ID=?
        WHERE Book_ID=?
    ");
    $stmt->bind_param("sssiii", $title, $author, $isbn, $category, $publisher, $id);
    
    if ($stmt->execute()) {
        header("Location: books.php");
        exit;
    } else {
        $error = "Failed to update book.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>LMS | Edit Book</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex text-sm">

<?php include "../includers/sidebar.php"; ?>

<div class="flex-1 flex flex-col min-h-screen ml-64">
    <?php include "../includers/headers.php"; ?>

    <main class="p-8">
        <div class="max-w-2xl mx-auto bg-white p-8 rounded-lg shadow-md">
            <h1 class="text-2xl font-bold mb-6 text-gray-800 border-b pb-4">Edit Book Details</h1>
            
            <form method="POST" class="space-y-4">
                
                <div>
                    <label class="block text-gray-700 font-medium mb-1">Book Title</label>
                    <input type="text" name="title" required value="<?= $book['Title'] ?>"
                           class="w-full p-2 border border-gray-300 rounded focus:ring-2 focus:ring-blue-400 focus:outline-none">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-gray-700 font-medium mb-1">Author</label>
                        <input type="text" name="author" required value="<?= $book['Author'] ?>"
                               class="w-full p-2 border border-gray-300 rounded focus:ring-2 focus:ring-blue-400 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-gray-700 font-medium mb-1">ISBN</label>
                        <input type="text" name="isbn" required value="<?= $book['ISBN'] ?>"
                               class="w-full p-2 border border-gray-300 rounded focus:ring-2 focus:ring-blue-400 focus:outline-none">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-gray-700 font-medium mb-1">Category</label>
                        <select name="category" required 
                                class="w-full p-2 border border-gray-300 rounded focus:ring-2 focus:ring-blue-400 focus:outline-none bg-white">
                            <?php while ($c = $categories->fetch_assoc()) { ?>
                                <option value="<?= $c['Category_ID'] ?>" <?= ($c['Category_ID'] == $book['Category_ID']) ? 'selected' : '' ?>>
                                    <?= $c['Category_Name'] ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-gray-700 font-medium mb-1">Publisher</label>
                        <select name="publisher" required 
                                class="w-full p-2 border border-gray-300 rounded focus:ring-2 focus:ring-blue-400 focus:outline-none bg-white">
                            <?php while ($p = $publishers->fetch_assoc()) { ?>
                                <option value="<?= $p['Publisher_ID'] ?>" <?= ($p['Publisher_ID'] == $book['Publisher_ID']) ? 'selected' : '' ?>>
                                    <?= $p['Publisher_Name'] ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>
                </div>

                <div class="pt-4 flex justify-end gap-3">
                    <a href="books.php" class="px-4 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300 transition">Cancel</a>
                    <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 shadow transition font-medium">
                        Update Book
                    </button>
                </div>

            </form>
        </div>
    </main>
</div>

</body>
</html>
