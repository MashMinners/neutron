<?php
#Работа со списком историй болезни
//Заливает в базу данные по случаям стационара
$this->get('histories', '\Application\ExcelUploader\Controllers\MedicalHistoriesExcelUploadController::index');
#Работа с реестром счетов стоматологов
//Заливка реестра по стоматологии в БД
$this->get('stom/reestr', '\Application\ExcelUploader\Controllers\STOMRegisterExcelUploadController::index');
//Заливка посещений по стоматологии в БД
$this->get('stom/visits', '\Application\ExcelUploader\Controllers\STOMVisitsExcelUploadController::index');
//Поиск пересечений в 30-ти дневный период между посещениями и случаям ипопавшими в реестр
$this->get('stom/intersections', '\Application\IntersectionsFinder\Controllers\STOMRegisterIntersectionsFinderController::index');
#Работа с реестрами счетов по диспансеризации
//Заливка реестра 1 этапа диспансеризации в БД
$this->get('dp/registry', '\Application\ExcelUploader\Controllers\DPRegisterExcelUploadController::index');
$this->get('dp/intersections', '\Application\IntersectionsFinder\Controllers\DPRegisterIntersectionsFinderController::index');
//Заливка реестра 2 эатапа диспансеризации
$this->get('dv/registry', '\Application\ExcelUploader\Controllers\DVRegisterExcelUploadController::index');
$this->get('dv/intersections', '\Application\IntersectionsFinder\Controllers\DVRegisterIntersectionsFinderController::index');



