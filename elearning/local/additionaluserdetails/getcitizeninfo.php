<?php
/**
 * Plugin version and other meta-data are defined here.
 *
 * @package     local_additionaluserdetails
 * @copyright   2025 Josue <ninijosue123@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */
require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/lib.php');

$nationalid = required_param('nationalId', PARAM_TEXT);

$client  = new \core\http_client();
$pluginconfig = local_additionaluserdetails_get_config();

$loginurl = $pluginconfig['loginurl'];
$citizenurl = $pluginconfig['citizenurl'];
$username = $pluginconfig['username'];
$password = $pluginconfig['password'];
$keyphrase = $pluginconfig['keyphrase'];

// headers
$headers = [
    'Content-type' => 'application/json',
    'Accept' => 'application/json',
];

// Get the token from the request
$tokenresponse = $client->post($loginurl, [
    'headers' => $headers,
    'body' => json_encode([
        'username' => $username,
        'password' => $password,
    ])
]);
if ($tokenresponse->getStatusCode() !== 200) {
    echo json_encode(['status' =>$tokenresponse->getStatusCode() , 'message' => 'Failed to retrieve token.']);
    exit;
}
$token = json_decode($tokenresponse->getBody(), true) ?? '';

// Get citizen information
$citizenresponse = $client->post($citizenurl, [
    'headers' => array_merge($headers, [
        'Authorization' => $token,
    ]),
    'body' => json_encode([
        'keyphrase' => $keyphrase,
        'documentNumber' => $nationalid,
    ])
]);

switch ($citizenresponse->getStatusCode()) {
    case 200:
        $citizeninfo = json_decode($citizenresponse->getBody(), true);
        echo json_encode(['status' => 'success', 'data' => $citizeninfo]);
        break;
    case 400:
        echo json_encode(['status' => 'error', 'message' => 'Invalid request.']);
        break;
    case 404:
        echo json_encode(['status' => 'error', 'message' => 'No information found for the provided national ID.']);
        break;
    case 500:
        echo json_encode(['status' => 'error', 'message' => 'Something went wrong, please try again later.']);
        break;
    default:
        echo json_encode(['status' => 'error', 'message' => 'An unexpected error occurred.']);
        break;
 exit;
}