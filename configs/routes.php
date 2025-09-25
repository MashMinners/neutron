<?php
#ИСТОРИИ БОЛЕЗНИ
//Заливает в базу данные по случаям стационара
$this->get('histories/upload', '\Application\ExcelUploader\Controllers\MedicalHistoriesExcelUploadController::upload');

#ПОСЕЩЕНИЯ ПОЛИКЛИНИКИ
$this->get('visits/upload', '\Application\ExcelUploader\Controllers\VisitsExcelUploadController::upload');
$this->delete('visits/truncate', '\Application\ExcelUploader\Controllers\VisitsExcelUploadController::truncate');

#РЕЕСТРЫ СЧЕТОВ ПО СТОМАТОЛОГИИ


#РЕЕСТРЫ СЧЕТОВ ПО ДИСПАНСЕРИЗАЦИИ


//Поиск разорванных случаев, являются дубликатами записей по полису
$this->get('buffer/registry/duplicates', '\Application\Registry\Controllers\STOMRegistryController::findDuplicates');


#РАБОТА С БУФЕРНОЙ ТАБЛИЦЕЙ
//Загрузка реестров
$this->get('buffer/disp/upload', '\Application\ExcelUploader\Controllers\BufferDISPRegistryExcelUploaderController::upload');
$this->get('buffer/stom/upload', '\Application\ExcelUploader\Controllers\BufferSTOMRegistryExcelUploaderController::upload');
//Очистка буфера
$this->delete('buffer/stom/truncate', '\Application\ExcelUploader\Controllers\BufferSTOMRegistryExcelUploaderController::truncate');
//Пересечения
$this->get('buffer/disp/intersections', '\Application\IntersectionsFinder\Controllers\DISPRegisterIntersectionsFinderController::find');
$this->get('buffer/stom/intersections', '\Application\IntersectionsFinder\Controllers\BufferSTOMRegistryIntersectionsFinderController::find');
$this->get('buffer/stom/purposes', '\Application\IntersectionsFinder\Controllers\BufferSTOMRegistryIntersectionsFinderController::findIncorrectPurposes');


