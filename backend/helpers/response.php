<?php
function json_response($data, $status_code = 200) {
    http_response_code($status_code);
    header('Content-Type: application/json; charset=utf-8');
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');
    echo json_encode($data);
    exit;
}

function json_error($message, $status_code = 400) {
    json_response(['success' => false, 'error' => $message], $status_code);
}

function json_success($data = [], $message = '') {
    $response = ['success' => true];
    if ($message) $response['message'] = $message;
    if ($data)    $response['data'] = $data;
    json_response($response, 200);
}
?>