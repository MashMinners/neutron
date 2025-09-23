<?php
#ИСТОРИИ БОЛЕЗНИ
//Заливает в базу данные по случаям стационара
$this->get('histories', '\Application\ExcelUploader\Controllers\MedicalHistoriesExcelUploadController::index');

#РЕЕСТРЫ СЧЕТОВ ПО СТОМАТОЛОГИИ
//Заливка реестра по стоматологии в БД
$this->get('stom//registry/upload', '\Application\ExcelUploader\Controllers\STOMRegisterExcelUploadController::upload');
//Заливка посещений по стоматологии в БД
$this->get('stom/visits', '\Application\ExcelUploader\Controllers\STOMVisitsExcelUploadController::index');
//Поиск пересечений в 30-ти дневный период между посещениями и случаям ипопавшими в реестр
$this->get('stom/intersections', '\Application\IntersectionsFinder\Controllers\STOMRegisterIntersectionsFinderController::index');

#РЕЕСТРЫ СЧЕТОВ ПО ДИСПАНСЕРИЗАЦИИ
//Заливка в буферную таблицу любого реестра
$this->get('buffer/registry/upload', '\Application\ExcelUploader\Controllers\BufferRegisterExcelUploadController::upload');
$this->delete('buffer/registry/truncate', '\Application\ExcelUploader\Controllers\BufferRegisterExcelUploadController::truncate');
//Заливка реестра 1 этапа диспансеризации в БД
$this->get('dp/registry/upload', '\Application\ExcelUploader\Controllers\DPRegisterExcelUploadController::upload');
$this->delete('dp/registry/truncate', '\Application\ExcelUploader\Controllers\DPRegisterExcelUploadController::truncate');
$this->get('dp/intersections/find', '\Application\IntersectionsFinder\Controllers\DISPRegisterIntersectionsFinderController::find');
//Заливка реестра 2 этапа диспансеризации
$this->get('dv/registry', '\Application\ExcelUploader\Controllers\DVRegisterExcelUploadController::index');
$this->get('dv/intersections', '\Application\IntersectionsFinder\Controllers\DVRegisterIntersectionsFinderController::index');
//Заливка реестра по проф. осмотрам 21 цель
$this->get('do/registry', '\Application\ExcelUploader\Controllers\DORegisterExcelUploadController::index');
$this->get('do/intersections', '\Application\IntersectionsFinder\Controllers\DORegisterIntersectionsFinderController::index');
//Заливка реестра по углубленной диспансеризации
$this->get('da/registry', '\Application\ExcelUploader\Controllers\DARegisterExcelUploadController::index');
$this->get('da/intersections', '\Application\IntersectionsFinder\Controllers\DARegisterIntersectionsFinderController::index');
//Заливка реестра репродуктивке 1 этап
$this->get('dpr/registry', '\Application\ExcelUploader\Controllers\DPRRegisterExcelUploadController::index');
$this->get('dpr/intersections', '\Application\IntersectionsFinder\Controllers\DPRRegisterIntersectionsFinderController::index');
//Заливка реестра репродуктивке 2 этап
$this->get('dvr/registry', '\Application\ExcelUploader\Controllers\DVRRegisterExcelUploadController::index');
$this->get('dvr/intersections', '\Application\IntersectionsFinder\Controllers\DVRRegisterIntersectionsFinderController::index');




