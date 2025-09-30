<?php
#ИСТОРИИ БОЛЕЗНИ
//Заливает в базу данные по случаям стационара
$this->get('histories/upload', '\Application\ExcelUploader\Controllers\MedicalHistoriesExcelUploadController::upload');
$this->delete('histories/truncate', '\Application\ExcelUploader\Controllers\MedicalHistoriesExcelUploadController::truncate');

#ПОСЕЩЕНИЯ ПОЛИКЛИНИКИ
$this->get('visits/upload', '\Application\ExcelUploader\Controllers\VisitsExcelUploadController::upload');
$this->delete('visits/truncate', '\Application\ExcelUploader\Controllers\VisitsExcelUploadController::truncate');




#РАБОТА С БУФЕРНОЙ ТАБЛИЦЕЙ СТОМАТОЛОГИИ
//Загрузка реестров
$this->get('buffer/stom/upload', '\Application\ExcelUploader\Controllers\BufferSTOMRegistryExcelUploaderController::upload');
//Очистка буфера
$this->delete('buffer/stom/truncate', '\Application\ExcelUploader\Controllers\BufferSTOMRegistryExcelUploaderController::truncate');
//Пересечения
$this->get('buffer/stom/intersections', '\Application\IntersectionsFinder\Controllers\BufferSTOMRegistryIntersectionsFinderController::find');
//Цели посещения 1.0/3.0
$this->get('buffer/stom/purposes', '\Application\IntersectionsFinder\Controllers\BufferSTOMRegistryIntersectionsFinderController::findIncorrectPurposes');
//Поиск разорванных случаев, являются дубликатами записей по полису
$this->get('buffer/stom/duplicates', '\Application\Registry\Controllers\STOMRegistryController::findDuplicates');
/**
 * Сначала ищем дубликаты - это разорванные случаи!
 * Потом объединяем их в один случай
 * Далее мы ищем PURPOSES чтобы знать где поменять цели с 1.0 на 3.0
 */

#РАБОТА С БУФЕРНОЙ ТАБЛИЦЕЙ ДИСПАНСЕРИЗАЦИИ
//Загрузка реестров
$this->get('buffer/disp/upload', '\Application\ExcelUploader\Controllers\BufferDISPRegistryExcelUploaderController::upload');
//Очистка буфера
$this->delete('buffer/disp/truncate', '\Application\ExcelUploader\Controllers\BufferDISPRegistryExcelUploaderController::truncate');
//Пересечения
$this->get('buffer/disp/intersections', '\Application\IntersectionsFinder\Controllers\BufferDISPRegistryIntersectionsFinderController::find');

#РАБОТА С БУФЕРНОЙ ТАБЛИЦЕЙ ПО ЛИСТКАМ НЕТРУДОСПОСОБНОСТИ
$this->get('ln/upload', '\Application\ExcelUploader\Controllers\SickNoteExcelUploaderController::upload');
$this->get('ln/truncate', '\Application\ExcelUploader\Controllers\SickNoteExcelUploaderController::truncate');
$this->get('ln/intersections', '\Application\IntersectionsFinder\Controllers\SickNoteIntersectionsFinderController::find');


