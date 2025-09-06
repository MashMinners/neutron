<?php
//
$this->get('excel', '\Application\ExcelUploader\Controllers\MedicalHistoryExcelUploadController::index');
$this->get('histories', '\Application\ExcelUploader\Controllers\MedicalHistoryExcelUploadController::getMedicalHistories');
//Работа с реестром счетов стоматологов
$this->get('stom/reestr', '\Application\ExcelUploader\Controllers\STOMRegisterExcelUploadController::index');
$this->get('stom/visits', '\Application\ExcelUploader\Controllers\STOMVisitsExcelUploadController::index');
$this->get('stom/intersections', 'Application\IntersectionsFinder\Controllers\STOMRegisterIntersectionsFinderController::index');

