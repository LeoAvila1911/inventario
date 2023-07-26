<?php

class Zenodo
{
    public function obtenerApiKey($clientID, $clientSecret, $authURL)
    {

        // Datos requeridos para el flujo de "Client Credentials Grant"
        $grantType = 'client_credentials';
        $scope = 'deposit:write deposit:actions';

        // Inicializar cURL
        $curl = curl_init($authURL);

        // Configurar opciones de cURL
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, [
            'Content-Type: application/x-www-form-urlencoded'
        ]);
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query([
            'grant_type' => $grantType,
            'scope' => $scope,
            'client_id' => $clientID,
            'client_secret' => $clientSecret
        ]));

        try {
            // Realizar la solicitud POST para obtener el token de acceso
            $response = curl_exec($curl);
            $statusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

            // Verificar el código de estado de la respuesta
            if ($statusCode === 200) {
                $responseData = json_decode($response, true);
                $accessToken = $responseData['access_token'];
                return $accessToken;
            } else {
                echo 'Error al conectar con el API de Zenodo. Código de estado: ' . $statusCode;
            }
        } catch (Exception $e) {
            echo 'Error de conexión: ' . $e->getMessage();
        }

        // Cerrar la conexión cURL
        curl_close($curl);
    }

    public function verificarConexion($apiKey, $depositURL)
    {

        // Inicializar cURL
        $curl = curl_init($depositURL);

        // Configurar opciones de cURL
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $apiKey
        ]);

        try {
            // Realizar la solicitud GET
            $response = curl_exec($curl);
            $statusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

            // Verificar el código de estado de la respuesta
            if ($statusCode === 200) {
                echo 'Conexión exitosa con el API de Zenodo';
                return true;
            } else {
                echo 'Error al conectar con el API de Zenodo. Código de estado: ' . $statusCode;
                return false;
            }
        } catch (Exception $e) {
            echo 'Error de conexión: ' . $e->getMessage();
        }

        // Cerrar la conexión cURL
        curl_close($curl);
    }

    public function crearDeposito($apiKey, $depositURL, $nombre)
    {

        // Datos del nuevo depósito
        $title = "$nombre";
        $description = 'Descripción de mi nuevo depósito';
        $creators = array(
            array(
                'name' => 'Leonardo Ávila',
                'affiliation' => 'Estudiante de Ingeniería en Sistemas'
            )
        );

        // Configurar la solicitud CURL
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $depositURL);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_VERBOSE, true); /////
        curl_setopt($curl, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $apiKey
        ]);

        // Datos del nuevo depósito en formato JSON
        $data = array(
            'metadata' => array(
                'title' => $title,
                'upload_type' => 'other',
                'description' => $description,
                'creators' => $creators
            )
        );
        $jsonData = json_encode($data);

        // Agregar los datos JSON a la solicitud
        curl_setopt($curl, CURLOPT_POSTFIELDS, $jsonData);

        try {

            // Enviar la solicitud y obtener la respuesta
            $response = curl_exec($curl);
            $statusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

            // Verificar el código de estado de la respuesta
            if ($statusCode === 201) {
                $responseData = json_decode($response, true);
                $depositID = $responseData['id'];
                echo 'Nuevo depósito creada en Zenodo. ID de depósito: ' . $depositID;
            } else {
                echo 'Error al crear la depósito en Zenodo. Código de estado: ' . $statusCode;
            }
        } catch (Exception $e) {
            echo 'Error de conexión: ' . $e->getMessage();
        }
        // Cerrar la conexión CURL
        curl_close($curl);

        // Retorna el ID del depósito creado
        return $depositID;
    }

    public function cargarArchivo($apiKey, $uploadUrl, $depositID, $opcion, $nombre)
    {
        $uploadUrl = str_replace('{$depositId}', $depositID, $uploadUrl);

        // Ruta completa al archivo que deseas subir
        $filePath = "../descargas/$opcion $nombre";

        // Inicializar cURL
        $curl = curl_init();

        // Configurar opciones de cURL
        curl_setopt_array($curl, [
            CURLOPT_URL => $uploadUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => [
                'file' => new CURLFile($filePath),
            ],
            CURLOPT_HTTPHEADER => [
                'Content-Type: multipart/form-data',
                'Authorization: Bearer ' . $apiKey,
            ],
        ]);

        try {
            // Realizar la solicitud POST para subir el archivo
            $response = curl_exec($curl);
            $statusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

            // Verificar el código de estado de la respuesta
            if ($statusCode === 201) {
                echo 'Archivo subido exitosamente a Zenodo';
            } else {
                echo 'Error al subir el archivo a Zenodo. Código de estado: ' . $statusCode;
            }
        } catch (Exception $e) {
            echo 'Error de conexión: ' . $e->getMessage();
        }

        // Cerrar la conexión cURL
        curl_close($curl);
    }

    public function publicarDeposito($apiKey, $depositPublishURL, $depositID)
    {
        $depositPublishURL = str_replace('{$depositId}', $depositID, $depositPublishURL);

        // Configuración de la solicitud
        $curl = curl_init($depositPublishURL);
        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                "Authorization: Bearer {$apiKey}"
            ]
        ]);

        // Realizar la solicitud
        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        // Verificar el código de respuesta
        if ($httpCode === 202) {
            echo "Registro publicado exitosamente.";
        } else {
            echo "No se pudo publicar el registro. Código de estado: {$httpCode}";
        }

        // Cerrar la conexión cURL
        curl_close($curl);
    }

    public function eliminarDeposito($apiKey, $depositFileURL, $depositID)
    {

        $depositFileURL = str_replace('{$depositId}', $depositID, $depositFileURL);

        // Inicializar cURL
        $curl = curl_init();

        // Configurar opciones de cURL
        curl_setopt_array($curl, [
            CURLOPT_URL => $depositFileURL,
            CURLOPT_CUSTOMREQUEST => 'DELETE',
            CURLOPT_HTTPHEADER => [
                "Content-Type: application/json",
                "Authorization: Bearer {$apiKey}"
            ]
        ]);

        // Ejecutar la solicitud cURL
        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        // Verificar si la solicitud fue exitosa
        if ($httpCode === 204) {
            echo "El depósito ha sido eliminado con éxito.";
        } else {
            echo "No se pudo eliminar el depósito. Código HTTP: {$httpCode}.";
        }

        // Cerrar la conexión cURL
        curl_close($curl);
    }
}
