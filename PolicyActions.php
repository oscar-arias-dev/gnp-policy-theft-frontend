<?php

try {
	$host = '104.154.142.250';
	$username = 'srmotgnp24';
	$password = 'nj56q1npL93aG3eo';
	$dbname = 'gnp';
	$conn = new mysqli($host, $username, $password, $dbname);

	if ($conn->connect_error) {
		die("Connection failed: " . $conn->connect_error);
	}
	if ($_GET["action"] == "list") {
		$sql = 'SELECT * FROM policies';
		$dbPolicies = $conn->query($sql);
		$policies = [];
		while ($row = $dbPolicies->fetch_assoc()) {
			$policies[] = $row;
		}
		$jTableResult = array();
		$jTableResult['Result'] = "OK";
		$jTableResult['Records'] = $policies;
		print json_encode($jTableResult);
	} else if($_GET["action"] == "update") {
		$statusMap = [
			'RR' => 'Reporte de Robo',
			'EO' => 'En operativo',
			'LO' => 'Localizado',
			'RE' => 'Recuperado',
			'NR' => 'No recuperado',
			'CA' => 'Cancelado',
		];
		$code = isset($_GET["estado"]) ? $_GET["estado"] : "";
		$description = isset($statusMap[$code]) ? $statusMap[$code] : "";
		$fullStatus = $description ? $code . '-' . $description : $code;
		$siniestro = $_GET["codSinisestro"];
		$ci = curl_init();
    	$url = "https://api-gc-uat.service.gnp.com.mx/wfdlcore/api/proveedor/siniestros/estatus";
		$dataBody = [
			"comentarios" => "test",
			"estatus" => [
				"clave" => $code,
				"descripcion" => $description,
			],
			"listaSiniestros" => [
    			$siniestro
			],
		];
		$json_data = json_encode($dataBody);
		curl_setopt($ci, CURLOPT_URL, $url);
		curl_setopt($ci, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ci, CURLOPT_HEADER, false);
		curl_setopt($ci, CURLOPT_POST, true);
		curl_setopt($ci, CURLOPT_POSTFIELDS, $json_data);
		curl_setopt($ci, CURLOPT_HTTPHEADER, [
			"accept: application/json;charset=UTF-8",
			'tokenProvider: $2a$10$OnNNvsnJbjb9K8Tb.10NEOUE92juS16B.YW6fPnR78s6PRv/E.0we',
			"Content-Type: application/json;charset=UTF-8"
		]);
		$json = curl_exec($ci);
		if (curl_errno($ci)) {
			$errorMsg = curl_error($ci);
			$errorCode = curl_errno($ci);
			throw new Exception("cURL Error ($errorCode): $errorMsg");
		}
		$http_code = curl_getinfo($ci, CURLINFO_HTTP_CODE);
		if ($http_code == 200 || $http_code == 201) {
			$sql = "UPDATE policies SET status_robo = '" . $fullStatus . "' WHERE id = '" . $_GET["id"] . "'";
			$updatedPolicies = $conn->query($sql);
		} else {
			throw new Exception("Error al cambiar estado en GNP");
		}
	}
	$conn->close();
} catch (Exception $ex) {
	$jTableResult = array();
	$jTableResult['Result'] = "ERROR";
	$jTableResult['Message'] = $ex->getMessage();
	print json_encode($jTableResult);
}
