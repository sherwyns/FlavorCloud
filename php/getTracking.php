<?php
require_once("services/trackingService.php");
$trackingService = new trackingService();
$res = $trackingService->getTracking();
?>
