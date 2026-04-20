<?php
/**
 * Advanced API Mock Backend for ESP32 Smart Bell System
 */
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

$endpoint = isset($_GET['endpoint']) ? $_GET['endpoint'] : '';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

switch ($endpoint) {
    case 'status':
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            http_response_code(405);
            exit;
        }
        echo json_encode([
            "state" => "RUNNING",
            "time" => date("H:i:s")
        ]);
        break;

    case 'audio_list':
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            http_response_code(405);
            exit;
        }

        $audios = [];
        // Add defaults
        $audios = ["bell.mp3", "morning_prayer.mp3", "national_anthem.mp3", "exam_alert.mp3", "emergency_siren.wav"];

        // Scan audio directory if it exists
        if (is_dir("audio")) {
            $files = scandir("audio");
            foreach ($files as $f) {
                if ($f !== '.' && $f !== '..') {
                    if (!in_array($f, $audios)) {
                        $audios[] = $f;
                    }
                }
            }
        }

        echo json_encode($audios);
        break;

    case 'upload_audio':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            exit;
        }
        if (!isset($_FILES['audio_file'])) {
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => "No file uploaded"]);
            exit;
        }

        $fileName = basename($_FILES['audio_file']['name']);
        $fileSize = $_FILES['audio_file']['size'];
        $fileType = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        if ($fileType != "mp3" && $fileType != "wav") {
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => "Only MP3 & WAV files are allowed"]);
            exit;
        }

        if (!is_dir("audio")) {
            mkdir("audio", 0777, true);
        }

        if (move_uploaded_file($_FILES["audio_file"]["tmp_name"], "audio/" . $fileName)) {
            echo json_encode(["status" => "success", "message" => "File '$fileName' uploaded successfully.", "file" => $fileName]);
        } else {
            http_response_code(500);
            echo json_encode(["status" => "error", "message" => "Failed to move uploaded file. Check folder permissions."]);
        }
        break;

    case 'sync_time':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            exit;
        }
        $input = json_decode(file_get_contents('php://input'), TRUE);

        if (!isset($input['timestamp'])) {
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => "Missing device timestamp"]);
            exit;
        }

        // Mock hardware RTC operation here
        echo json_encode(["status" => "success", "message" => "RTC accurately synchronized via NTP or Host request to: " . $input['timestamp']]);
        break;

    case 'schedule':
        $dbFile = "database.json";
        
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            if (file_exists($dbFile)) {
                $db = json_decode(file_get_contents($dbFile), true);
                echo json_encode(isset($db['schedules']) ? $db['schedules'] : []);
            } else {
                echo json_encode([]);
            }
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $input = json_decode(file_get_contents('php://input'), TRUE);

            if (!is_array($input)) {
                http_response_code(400);
                echo json_encode(["status" => "error", "message" => "Invalid payload"]);
                exit;
            }

            $currentDb = file_exists($dbFile) ? json_decode(file_get_contents($dbFile), true) : [];
            if(!is_array($currentDb)) $currentDb = [];
            $currentDb['schedules'] = $input;
            
            file_put_contents($dbFile, json_encode($currentDb, JSON_PRETTY_PRINT));

            echo json_encode(["status" => "success", "message" => count($input) . " records saved effectively to configuration."]);
            exit;
        }
        
        http_response_code(405);
        break;

    default:
        http_response_code(404);
        echo json_encode(["status" => "error", "message" => "Endpoint not implemented"]);
        break;
}
