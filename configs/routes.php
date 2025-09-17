<?php
//Работа со списком историй болезни
$this->get('histories', '\Application\ExcelUploader\Controllers\MedicalHistoriesExcelUploadController::index');
//Работа с реестром счетов стоматологов
$this->get('stom/reestr', '\Application\ExcelUploader\Controllers\STOMRegisterExcelUploadController::index');
$this->get('stom/visits', '\Application\ExcelUploader\Controllers\STOMVisitsExcelUploadController::index');
$this->get('stom/intersections', 'Application\IntersectionsFinder\Controllers\STOMRegisterIntersectionsFinderController::index');
//Работа с реестром счетов по диспансеризации. 1 этап
$this->get('dp/registry', '\Application\ExcelUploader\Controllers\DPRegisterExcelUploadController::index');



