<?php
$files = [
    'c:/xampp/htdocs/Library-management/books/books.php',
    'c:/xampp/htdocs/Library-management/books/addBook.php',
    'c:/xampp/htdocs/Library-management/books/editBook.php',
    'c:/xampp/htdocs/Library-management/books/deleteBook.php',
    'c:/xampp/htdocs/Library-management/members/members.php',
    'c:/xampp/htdocs/Library-management/members/view_member_history.php',
    'c:/xampp/htdocs/Library-management/librarians/librarians.php',
    'c:/xampp/htdocs/Library-management/librarians/view_librarian_history.php',
    'c:/xampp/htdocs/Library-management/categories/categories.php',
    'c:/xampp/htdocs/Library-management/publishers/publishers.php',
    'c:/xampp/htdocs/Library-management/admin/email_logs.php'
];

$injection = "
// Staff Role Restriction
if (isset(\$_SESSION['admin_role']) && \$_SESSION['admin_role'] === 'staff') {
    header(\"Location: \" . BASE_URL . \"/dashboard/dashboard.php\");
    exit;
}
";

foreach ($files as $file) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        if (strpos($content, "'staff'") === false) {
            $content = preg_replace("/(if \(!isset\(\\\$_SESSION\['admin'\]\)\) \{[\s\S]*?exit;\s*\})/", "$1\n" . $injection, $content, 1);
            file_put_contents($file, $content);
            echo "Updated: $file\n";
        }
    } else {
        echo "Missing: $file\n";
    }
}
?>
