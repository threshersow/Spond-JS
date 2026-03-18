<?php
/**
 * spond-browser-client / spond-proxy.php
 * ========================================
 * Author:  Jonathan Puu
 * Project: https://github.com/jonathanpuu/spond-browser-client
 * License: GNU General Public License v3.0 (GPL-3.0)
 *
 * Based on / inspired by:
 *   Olen/Spond — https://github.com/Olen/Spond
 *   Copyright (C) Olen — GPL-3.0 License
 *
 * PHP reverse proxy for the Spond API.
 * Forwards browser requests to api.spond.com with proper CORS headers,
 * working around the browser same-origin policy restriction.
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the Free
 * Software Foundation, either version 3 of the License, or (at your option)
 * any later version.
 *
 * Deploy:
 *  1. Upload this file to your web host public_html folder
 *  2. Upload .htaccess to the SAME folder (fixes Apache stripping Authorization header)
 *  3. In spond-embed.html set:
 *       const API_BASE      = 'https://yourdomain.com/spond-proxy.php/core/v1/';
 *       const CHAT_API_BASE = 'https://yourdomain.com/spond-proxy.php/chat/v1/';
 *
 * Optional: set ALLOWED_ORIGIN to your site's URL to restrict access.
 */

define('ALLOWED_ORIGIN', '*');

header('Access-Control-Allow-Origin: '  . ALLOWED_ORIGIN);
header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, auth, X-Spond-Clubid');
header('Access-Control-Max-Age: 86400');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// ── Build target Spond URL ────────────────────────────────────────────────────
$script = $_SERVER['SCRIPT_NAME'];   // /spond-proxy.php
$uri    = $_SERVER['REQUEST_URI'];   // /spond-proxy.php/chat/v1/chats?max=20
$path   = substr($uri, strlen($script));  // /chat/v1/chats?max=20

$targetUrl = 'https://api.spond.com' . $path;

// ── Collect headers to forward ────────────────────────────────────────────────
$forwardHeaders = [];

// getallheaders() can miss Authorization on some Apache setups.
// Apache may pass it as HTTP_AUTHORIZATION or REDIRECT_HTTP_AUTHORIZATION instead.
$authHeader = '';
if (!empty($_SERVER['HTTP_AUTHORIZATION'])) {
    $authHeader = $_SERVER['HTTP_AUTHORIZATION'];
} elseif (!empty($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
    $authHeader = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
}

foreach (getallheaders() as $name => $value) {
    $lower = strtolower($name);
    if (in_array($lower, ['host', 'connection', 'content-length', 'authorization'])) continue;
    $forwardHeaders[] = "$name: $value";
}

// Always add Authorization from the most reliable source
if ($authHeader) {
    $forwardHeaders[] = "Authorization: $authHeader";
}

// ── Request body ──────────────────────────────────────────────────────────────
$body = file_get_contents('php://input');

// ── cURL ──────────────────────────────────────────────────────────────────────
$ch = curl_init($targetUrl);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTPHEADER     => $forwardHeaders,
    CURLOPT_CUSTOMREQUEST  => $_SERVER['REQUEST_METHOD'],
    CURLOPT_HEADER         => true,
    CURLOPT_SSL_VERIFYPEER => true,
    CURLOPT_TIMEOUT        => 30,
]);

if (!empty($body)) {
    curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
}

$response   = curl_exec($ch);
$httpCode   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
$curlError  = curl_error($ch);
curl_close($ch);

if ($response === false) {
    http_response_code(502);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Proxy fetch failed', 'detail' => $curlError]);
    exit;
}

$responseHeaders = substr($response, 0, $headerSize);
$responseBody    = substr($response, $headerSize);

foreach (explode("\r\n", $responseHeaders) as $line) {
    if (empty($line)) continue;
    $lower = strtolower($line);
    if (
        strpos($lower, 'access-control') === 0 ||
        strpos($lower, 'http/')          === 0 ||
        strpos($lower, 'transfer-encoding') === 0
    ) continue;
    if (strpos($line, ':') !== false) header($line, false);
}

http_response_code($httpCode);
echo $responseBody;
