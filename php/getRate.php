<?php
require_once("services/rateService.php");
$rateService = new rateService();
$res = $rateService->getRate();
?>
