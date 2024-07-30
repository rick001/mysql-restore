<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get database credentials from the POST request
    $dbHost = $_POST['dbHost'];
    $dbUser = $_POST['dbUser'];
    $dbPass = $_POST['dbPass'];
    $dbName = $_POST['dbName'];

    // Helper function to respond with JSON
    function respond($ok = 1, $info = "") {
        if ($ok == 0) {
            http_response_code(400);
        }
        exit(json_encode(["ok" => $ok, "info" => $info]));
    }

    // Check for file upload errors
    if (empty($_FILES) || $_FILES['file']['error']) {
        respond(0, "Failed to upload file.");
    }

    // Set upload directory
    $uploadDir = __DIR__ . DIRECTORY_SEPARATOR . 'uploads';
    if (!file_exists($uploadDir)) {
        if (!mkdir($uploadDir, 0777, true)) {
            respond(0, "Failed to create upload directory.");
        }
    }

    // Handle chunked uploads
    $fileName = $_FILES['file']['name'];
    $filePath = $uploadDir . DIRECTORY_SEPARATOR . $fileName;
    $chunk = isset($_POST['chunk']) ? intval($_POST['chunk']) : 0;
    $chunks = isset($_POST['chunks']) ? intval($_POST['chunks']) : 0;

    $out = fopen("{$filePath}.part", $chunk == 0 ? "wb" : "ab");
    if ($out) {
        $in = fopen($_FILES['file']['tmp_name'], "rb");
        if ($in) {
            while ($buff = fread($in, 4096)) {
                fwrite($out, $buff);
            }
        } else {
            respond(0, "Failed to open input stream.");
        }
        fclose($in);
        fclose($out);
        unlink($_FILES['file']['tmp_name']);
    } else {
        respond(0, "Failed to open output stream.");
    }

    // Combine chunks and process the file
    if (!$chunks || $chunk == $chunks - 1) {
        rename("{$filePath}.part", $filePath);

        // Unzip if needed
        $unzippedFilePath = $filePath;
        if (pathinfo($filePath, PATHINFO_EXTENSION) == 'zip') {
            $zip = new ZipArchive();
            if ($zip->open($filePath) === TRUE) {
                $zip->extractTo($uploadDir);
                $unzippedFilePath = $uploadDir . DIRECTORY_SEPARATOR . $zip->getNameIndex(0);
                $zip->close();
            } else {
                respond(0, "Failed to unzip file.");
            }
        }

        // Import SQL file in chunks
        $mysqli = new mysqli($dbHost, $dbUser, $dbPass, $dbName);
        if ($mysqli->connect_error) {
            respond(0, "Database connection failed: " . $mysqli->connect_error);
        }

        $sqlFile = fopen($unzippedFilePath, "r");
        if ($sqlFile) {
            $query = "";
            while ($line = fgets($sqlFile)) {
                if (substr(trim($line), 0, 2) == "--" || trim($line) == "") {
                    continue;
                }

                $query .= $line;
                if (substr(trim($line), -1, 1) == ";") {
                    if (!$mysqli->query($query)) {
                        respond(0, "Error executing query: " . $mysqli->error);
                    }
                    $query = "";
                }
            }
            fclose($sqlFile);
        } else {
            respond(0, "Failed to open SQL file.");
        }

        $mysqli->close();
    }

    respond(1, "Upload and import successful.");
}
?>
