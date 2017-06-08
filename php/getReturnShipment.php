<?php
require_once("services/returnShippingService.php");
$returnShippingService = new returnShippingService();
$res = $returnShippingService->getReturnShipment();
?>
