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
        $audios = ["bell.mp3", "morning_prayer.mp3", "national_anthem.mp3", "exam_alert.mp3", "emergency_siren.wav"];
        echo json_encode($audios);
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
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            exit;
        }
        $input = json_decode(file_get_contents('php://input'), TRUE);

        if (!is_array($input)) {
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => "Invalid payload"]);
            exit;
        }

        // Mock file writes or EEPROM commit for the JSON schedule sequence
        echo json_encode(["status" => "success", "message" => count($input) . " records saved effectively to configuration."]);
        break;

    default:
        http_response_code(404);
        echo json_encode(["status" => "error", "message" => "Endpoint not implemented"]);
        break;
}
?>