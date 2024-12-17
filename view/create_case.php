<?php
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $case_title = $_POST['caseTitle'];
    $client_name = $_POST['clientName'];
    $case_type = $_POST['caseType'];
    $case_description = $_POST['caseDescription'];

    $sql = "INSERT INTO cases (case_title, client_name, case_type, case_description)
            VALUES ('$case_title', '$client_name', '$case_type', '$case_description')";

    if ($conn->query($sql) === TRUE) {
        echo "New case created successfully";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }

    $conn->close();
}
?>
