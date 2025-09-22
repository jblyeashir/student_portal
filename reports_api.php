<?php
require_once "functions.php";

// Stage distribution
$stmt = $db->query("SELECT s.title, COUNT(a.id) as total 
                    FROM applications a 
                    LEFT JOIN stages s ON a.current_stage_id = s.id 
                    GROUP BY s.id");
$stages = ["labels"=>[], "counts"=>[]];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $stages["labels"][] = $row['title'] ?? "Not Started";
    $stages["counts"][] = $row['total'];
}

// Documents distribution
$stmt = $db->query("SELECT dt.title, COUNT(d.id) as total 
                    FROM documents d 
                    JOIN doc_types dt ON d.doc_type_id=dt.id 
                    GROUP BY dt.id");
$docs = ["labels"=>[], "counts"=>[]];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $docs["labels"][] = $row['title'];
    $docs["counts"][] = $row['total'];
}

// Applications by Country
$stmt = $db->query("SELECT s.country, COUNT(a.id) as total
                    FROM applications a
                    JOIN students s ON a.student_id = s.id
                    GROUP BY s.country");
$countries = ["labels"=>[], "counts"=>[]];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $countries["labels"][] = $row['country'] ?: "Unknown";
    $countries["counts"][] = $row['total'];
}

header("Content-Type: application/json");
echo json_encode([
    "stages" => $stages,
    "docs" => $docs,
    "countries" => $countries
]);
