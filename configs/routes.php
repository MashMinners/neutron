<?php
//
//$this->get('/', '\Application\Collector\Controllers\CollectorController::show');
$this->get('excel', '\Application\ExcelUploader\Controllers\MedicalHistoryExcelUploadController::index');
$this->get('histories', '\Application\ExcelUploader\Controllers\MedicalHistoryExcelUploadController::getMedicalHistories');

