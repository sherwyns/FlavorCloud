<?php
require_once("services/shippingService.php");
$shippingService = new shippingService();
$res = $shippingService->createShipment();
?>
