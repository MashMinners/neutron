<?php
#ИСТОРИИ БОЛЕЗНИ
//Заливает в базу данные по случаям стационара
$this->get('histories/upload', '\Application\ExcelUploader\Controllers\MedicalHistoriesExcelUploadController::upload');
//Очищение таблиув с ИБ по стационару
$this->delete('histories/truncate', '\Application\ExcelUploader\Controllers\MedicalHistoriesExcelUploadController::truncate');

#ПОСЕЩЕНИЯ ПОЛИКЛИНИКИ
$this->get('visits/upload', '\Application\ExcelUploader\Controllers\VisitsExcelUploadController::upload');
$this->delete('visits/truncate', '\Application\ExcelUploader\Controllers\VisitsExcelUploadController::truncate');

#ЗАГРУЗКА ФАЙЛОВ
$this->get('file/upload', '\Application\FileUploader\Controllers\FileUploaderController::upload');


#РАБОТА С БУФЕРНОЙ ТАБЛИЦЕЙ СТОМАТОЛОГИИ
//Загрузка реестров
$this->get('buffer/stom/upload', '\Application\ExcelUploader\Controllers\BufferSTOMRegistryExcelUploaderController::upload');
//Очистка буфера
$this->delete('buffer/stom/truncate', '\Application\ExcelUploader\Controllers\BufferSTOMRegistryExcelUploaderController::truncate');
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

#РАБОТА С ПАРСЕРОМ XML
$this->get('xml/parse', '\Application\XMLParser\Controllers\XMLParserController::parse');
$this->get('xml/stom/upload', '\Application\XMLParser\Controllers\StomXMLUploaderController::upload');
$this->delete('xml/stom/truncate', '\Application\XMLParser\Controllers\StomXMLUploaderController::truncate');

#РАБОТА С СМО
//$this->get('smo/parse/disp/cmis', '\Application\XlsParser\Controllers\SMO\CmisDispExelUploaderController::upload');
$this->get('smo/analyze/stom', '\Application\SMO\Controllers\ExcelSTOMAnalyzerController::analyze');
$this->get('smo/analyze/disp', '\Application\SMO\Controllers\ExcelDispAnalyzerController::analyze');
$this->get('smo/analyze/exam', '\Application\SMO\Controllers\ExcelExamAnalyzerController::analyze');
$this->get('smo/analyze/dpr', '\Application\SMO\Controllers\ExcelDPRAnalyzerController::analyze');

#РЕЕСТРЫ СЧЕТОВ. СТОМАТОЛОГИЯ
//Поиск не корректных целей посещения 3.0/1.0
$this->get('invoices/stom/incorrect-purposes', '\Application\Invoices\STOM\Controllers\IncorrectPurposeFinderController::find');
//Поиск не корректных услуг. Либо не одной первичной услуги в случае, либо 2 и более первичных услуг
$this->get('invoices/stom/incorrect-services', '\Application\Invoices\STOM\Controllers\IncorrectServicesFinderController::find');
//Поискне корректных диагнозов по отношению к пролеченным зубам
$this->get('invoices/stom/incorrect-teeth', '\Application\Invoices\STOM\Controllers\IncorrectTeethFinderController::find');
//Поиск пересечений случаев за 30 дневный период
$this->get('invoices/stom/intersections', '\Application\Invoices\STOM\Controllers\IntersectionsFinderController::find');
//Поиск разорванных случаев, когда на 1 пациента 2 и более случаев за 30 дней
$this->get('invoices/stom/torn-cases', '\Application\Invoices\STOM\Controllers\TornCaseFinderController::find');