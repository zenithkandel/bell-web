<?php
/**
 * API Mock Backend for ESP32 Bell System configuration
 * To be ported to C/C++ on firmware. 
 */

header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");

$endpoint = isset($_GET['endpoint']) ? $_GET['endpoint'] : '';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

function getMockState()
{
    return [
        "state" => "RUNNING",
        "time" => date("H:i:s")
    ];
}

function getMockAudio()
{
    return ["bell.mp3", "morning.mp3", "evening.mp3", "emergency.wav"];
}

switch ($endpoint) {
    case 'status':
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            http_response_code(405);
            echo json_encode(["error" => "Method not allowed"]);
            break;
        }
        echo json_encode(getMockState());
        break;

    case 'audio_list':
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            http_response_code(405);
            echo json_encode(["error" => "Method not allowed"]);
            break;
        }
        echo json_encode(getMockAudio());
        break;

    case 'sync_time':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(["error" => "Method not allowed"]);
            break;
        }
        $inputJSON = file_get_contents('php://input');
        $input = json_decode($inputJSON, TRUE);

        if (!isset($input['timestamp'])) {
            http_response_code(400);
            echo json_encode(["error" => "Missing timestamp"]);
            break;
        }

        echo json_encode(["success" => true, "message" => "Time synchronized"]);
        break;

    case 'schedule':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(["error" => "Method not allowed"]);
            break;
        }
        echo json_encode(["success" => true, "message" => "Schedule updated successfully"]);
        break;

    default:
        http_response_code(404);
        echo json_encode(["error" => "Endpoint not found"]);
        break;
}
?>