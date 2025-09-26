<?php
// DB connection
$conn = new mysqli("localhost", "root", "", "smart_surplus");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Delete provider if requested
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM users WHERE id=$id AND role='provider'");
    header("Location: providers.php");
    exit();
}

// Fetch all providers
$result = $conn->query("SELECT * FROM users WHERE role='provider'");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Provider Management</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 p-8">
    <div class="bg-white shadow-md rounded-lg p-6">
        <h2 class="text-2xl font-bold mb-4">Canteen Providers</h2>
        <table class="min-w-full bg-white border">
            <thead>
                <tr class="bg-gray-200 text-gray-600 uppercase text-sm">
                    <th class="py-3 px-4 border">ID</th>
                    <th class="py-3 px-4 border">Username</th>
                    <th class="py-3 px-4 border">Email</th>
                    <th class="py-3 px-4 border">Subrole</th>
                    <th class="py-3 px-4 border">Created At</th>
                    <th class="py-3 px-4 border">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()) { ?>
                <tr class="text-gray-700">
                    <td class="py-2 px-4 border"><?php echo $row['id']; ?></td>
                    <td class="py-2 px-4 border"><?php echo $row['username']; ?></td>
                    <td class="py-2 px-4 border"><?php echo $row['email']; ?></td>
                    <td class="py-2 px-4 border"><?php echo $row['subrole']; ?></td>
                    <td class="py-2 px-4 border"><?php echo $row['created_at']; ?></td>
                    <td class="py-2 px-4 border">
                        <a href="?delete=<?php echo $row['id']; ?>" 
                           class="bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600"
                           onclick="return confirm('Are you sure you want to delete this provider?')">
                           Delete
                        </a>
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</body>
</html>
