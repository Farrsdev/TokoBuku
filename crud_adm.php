<?php

require_once 'config.php';
function query($sql)
{
    global $conn;
    $result = mysqli_query($conn, $sql);

    // Cek jika hasil bukan boolean
    if ($result === true || $result === false) {
        return $result;
    }

    $rows = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $rows[] = $row;
    }

    return $rows;
}


function search($keyword)
{
    $query = "SELECT * FROM books WHERE title LIKE '%$keyword%' OR category LIKE '%$keyword%'";
    return query($query);
}
function searchUser($keyword)
{
    $query = "SELECT * FROM users WHERE username LIKE '%$keyword%' OR email LIKE '%$keyword%'";
    return query($query);
}

function insert($data)
{
    global $conn;

    if (!isset($data["title"], $data["author"], $data["price"], $data["stock"], $data["category"])) {
        echo "<script>alert('Semua data harus di isi'); window.location.href = 'books_management.php';</script>";
        return false;
    }

    $title = htmlspecialchars($data["title"]);
    $author = htmlspecialchars($data["author"]);
    $price = htmlspecialchars($data["price"]);
    $stock = htmlspecialchars($data["stock"]);
    $category = htmlspecialchars($data["category"]);
    $image = upload();

    if ($image === false) {
        return false;
    }

    $query = "INSERT INTO books (title, author, price, stock, category, image)
              VALUES ('$title', '$author', '$price', '$stock', '$category', '$image')";

    mysqli_query($conn, $query);
    return mysqli_affected_rows($conn);
}

function delete($id)
{
    global $conn;
    $query = "DELETE FROM books WHERE id = '$id'";
    $result = mysqli_query($conn, $query);

    if ($result) {
        echo "<script>alert('Data berhasil dihapus'); window.location.href = 'dashboard_adm.php';</script>";
    } else {
        echo "<script>alert('Gagal menghapus data!');</script>";
    }
}

function update($data, $id)
{
    global $conn;

    $title = htmlspecialchars($data["title"]);
    $author = htmlspecialchars($data["author"]);
    $price = htmlspecialchars($data["price"]);
    $stock = htmlspecialchars($data["stock"]);
    $category = htmlspecialchars($data["category"]);

    if ($_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $image = upload();
        if (!$image) {
            return false;
        }
    } else {
        $result = mysqli_query($conn, "SELECT image FROM books WHERE id = '$id'");
        $row = mysqli_fetch_assoc($result);
        $image = $row['image'];
    }

    $query = "UPDATE books SET 
                title = '$title',
                author = '$author',
                price = '$price',
                stock = '$stock',
                category = '$category',
                image = '$image'
              WHERE id = '$id'";

    mysqli_query($conn, $query);
    return mysqli_affected_rows($conn);
}


function insertUser($data)
{
    global $conn;

    if (!isset($data['username'], $data['email'], $data['password'], $data['role'])) {
        echo "<script>alert('Semua data harus di isi'); window.location.href = 'users_management.php';</script>";
        return false;
    }
    $username = htmlspecialchars($data['username']);
    $email = htmlspecialchars($data['email']);
    $pass = htmlspecialchars($data['password']);
    $role = htmlspecialchars($data['role']);

    $query = "INSERT INTO users (username, email, password, role) VALUES ('$username', '$email', '$pass', '$role')";
    mysqli_query($conn, $query);
    return mysqli_affected_rows($conn);
}

function updateUser($data, $id)
{
    global $conn;
    $username = htmlspecialchars($data['username']);
    $email = htmlspecialchars($data['email']);
    $pass = htmlspecialchars($data['password']);
    $role = htmlspecialchars($data['role']);

    $query = "UPDATE users SET username = '$username', email = '$email', password = '$pass', role = '$role' WHERE id = '$id'";
    mysqli_query($conn, $query);
    return mysqli_affected_rows($conn);
}

function deleteUser($id)
{
    global $conn;
    $query = "DELETE FROM users WHERE id = '$id'";
    $result = mysqli_query($conn, $query);

    if ($result) {
        echo "<script>alert('Data berhasil dihapus'); window.location.href = 'dashboard_adm.php';</script>";
    } else {
        echo "<script>alert('Gagal menghapus data!');</script>";
    }
}

function upload()
{
    $file = $_FILES['image'];
    if ($file['error'] === 4) {
        echo "Tidak ada file yang diunggah.";
        return false;
    }

    $ekstensiValid = ['jpg', 'jpeg', 'png', 'webp', 'svg'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $ekstensiValid)) {
        echo "Ekstensi file tidak valid. Hanya diperbolehkan: jpg, jpeg, png, webp, svg.";
        return false;
    }

    if ($file['size'] > 2000000) {
        echo "Ukuran file terlalu besar. Maksimal 2MB.";
        return false;
    }

    $folderPath = __DIR__ . '/uploads/';
    $namaBaru = uniqid() . '.' . $ext;
    $pathUpload = $folderPath . $namaBaru;

    if (!move_uploaded_file($file['tmp_name'], $pathUpload)) {
        echo "Gagal memindahkan file.";
        return false;
    }

    // Simpan path relatif untuk ditampilkan di web
    return 'uploads/' . $namaBaru;
}
