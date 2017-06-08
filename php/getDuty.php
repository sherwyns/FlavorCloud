<?php
require_once("services/dutiesService.php");
$dutiesService = new dutiesService();
$res = $dutiesService->getLandedCost();
?>
