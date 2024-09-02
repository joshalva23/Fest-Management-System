<?php

/**
 * Sends an email confirmation via a Flask endpoint asynchronously.
 *
 * @param string $flaskUrl The URL of the Flask endpoint.
 * @param string $recipient The recipient's email address.
 * @param string $subject The subject of the email.
 * @param string $message The body of the email.
 *
 * @return void
 */
function sendEmailConfirmationAsync($recipient, $subject, $message)
{
    $data = [
        'recipient' => $recipient,
        'subject' => $subject,
        'message' => $message,
    ];

    $url = 'http://localhost:5000/send-email';
    $postFields = json_encode($data);

    // Create a cURL multi handle
    $multiCurl = curl_multi_init();
    $curlHandles = [];

    // Create a cURL handle for the request
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    
    // Add the handle to the multi handle
    curl_multi_add_handle($multiCurl, $ch);
    $curlHandles[] = $ch;

    // Execute the multi handle
    $running = null;
    do {
        curl_multi_exec($multiCurl, $running);
        usleep(100000); // Sleep for 100ms to avoid busy-waiting
    } while ($running > 0);

    // Check for errors and close handles
    foreach ($curlHandles as $ch) {
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($httpCode != 200) {
            $response = curl_multi_getcontent($ch);
            error_log("Failed to send email. HTTP code: $httpCode, Response: $response");
        }
        curl_multi_remove_handle($multiCurl, $ch);
        curl_close($ch);
    }

    // Close the multi handle
    curl_multi_close($multiCurl);
}

