<?php
#ИСТОРИИ БОЛЕЗНИ
//Заливает в базу данные по случаям стационара
//$this->get('histories/upload', '\Application\ExcelUploader\Controllers\MedicalHistoriesExcelUploadController::upload');
//Очищение таблиув с ИБ по стационару
//$this->delete('histories/truncate', '\Application\ExcelUploader\Controllers\MedicalHistoriesExcelUploadController::truncate');

#ПОСЕЩЕНИЯ ПОЛИКЛИНИКИ
//$this->get('visits/upload', '\Application\ExcelUploader\Controllers\VisitsExcelUploadController::upload');
//$this->delete('visits/truncate', '\Application\ExcelUploader\Controllers\VisitsExcelUploadController::truncate');

#ЗАГРУЗКА ФАЙЛОВ
//$this->get('file/upload', '\Application\FileUploader\Controllers\FileUploaderController::upload');


#РАБОТА С БУФЕРНОЙ ТАБЛИЦЕЙ СТОМАТОЛОГИИ
//Загрузка реестров
//$this->get('buffer/stom/upload', '\Application\ExcelUploader\Controllers\BufferSTOMRegistryExcelUploaderController::upload');
//Очистка буфера
//$this->delete('buffer/stom/truncate', '\Application\ExcelUploader\Controllers\BufferSTOMRegistryExcelUploaderController::truncate');
/**
 * Сначала ищем дубликаты - это разорванные случаи!
 * Потом объединяем их в один случай
 * Далее мы ищем PURPOSES чтобы знать где поменять цели с 1.0 на 3.0
 */

#РАБОТА С БУФЕРНОЙ ТАБЛИЦЕЙ ДИСПАНСЕРИЗАЦИИ
//Загрузка реестров
//$this->get('buffer/disp/upload', '\Application\ExcelUploader\Controllers\BufferDISPRegistryExcelUploaderController::upload');
//Очистка буфера
//$this->delete('buffer/disp/truncate', '\Application\ExcelUploader\Controllers\BufferDISPRegistryExcelUploaderController::truncate');
//Пересечения
//$this->get('buffer/disp/intersections', '\Application\IntersectionsFinder\Controllers\BufferDISPRegistryIntersectionsFinderController::find');

#РАБОТА С БУФЕРНОЙ ТАБЛИЦЕЙ ПО ЛИСТКАМ НЕТРУДОСПОСОБНОСТИ
//$this->get('ln/upload', '\Application\ExcelUploader\Controllers\SickNoteExcelUploaderController::upload');
//$this->get('ln/truncate', '\Application\ExcelUploader\Controllers\SickNoteExcelUploaderController::truncate');
//$this->get('ln/intersections', '\Application\IntersectionsFinder\Controllers\SickNoteIntersectionsFinderController::find');

#РАБОТА С ПАРСЕРОМ XML
//$this->get('xml/parse', '\Application\XMLParser\Controllers\XMLParserController::parse');
//$this->get('xml/stom/upload', '\Application\XMLParser\Controllers\StomXMLUploaderController::upload');
//$this->delete('xml/stom/truncate', '\Application\XMLParser\Controllers\StomXMLUploaderController::truncate');

#РАБОТА С СМО
//Генерирует файлы счетов в СМО по 14 форме
$this->get('smo/invoice/aggregate', '\Application\SMO\Form14\Controllers\Form14AggregatorController::aggregate');

#РАБОТА С ТФОМС
$this->get('tfoms/billing-validator/validate/stac', '\Application\TFOMS\MedicalBillingValidator\STAC\Controllers\ValidatorController::validate');
$this->get('tfoms/billing-validator/match/stac', '\Application\TFOMS\MedicalBillingValidator\STAC\Controllers\GuaranteedPaymentsMatcherController::match');
$this->get('tfoms/distribute', '\Application\TFOMS\TargetGroupDistributor\Controllers\PatientTargetGroupDistributorController::distribute');

#CMIS. ВАЛИДАЦИЯ РЕЕСТРОВ СЧЕТОВ. ДИСПАНСЕРИЗАЦИЯ
//Валидирует услуги предоставленные в XML из CMIS со списком услуг из справочника ТФОМС по 1 этапу диспансеризации и проф. осмотрам
$this->get('cmis/invoices/validate/dp', '\Application\CMIS\InvoiceServiceValidator\DISP\Controllers\DPInvoiceValidatorController::validate');
//Валидирует услуги предоставленные в XML из CMIS со списком услуг из справочника ТФОМС по углубленной диспансеризации
$this->get('cmis/invoices/validate/da', '\Application\CMIS\InvoiceServiceValidator\DISP\Controllers\DAInvoiceValidatorController::validate');

#CMIS. ВАЛИДАЦИЯ РЕЕСТРОВ СЧЕТОВ. СТОМАТОЛОГИЯ
$this->get('cmis/invoices/stom/intersections', '\Application\CMIS\InvoiceServiceValidator\STOM\Controllers\IntersectionsFinderController::find');
$this->get('cmis/invoices/stom/incorrect-purposes', '\Application\CMIS\InvoiceServiceValidator\STOM\Controllers\IncorrectPurposeFinderController::find');
$this->get('cmis/invoices/stom/torn-cases', '\Application\CMIS\InvoiceServiceValidator\STOM\Controllers\TornCasesFinderController::find');
$this->get('cmis/invoices/stom/incorrect-services', '\Application\CMIS\InvoiceServiceValidator\STOM\Controllers\IncorrectServicesFinderController::find');
$this->get('cmis/invoices/stom/simultaneous-teeth', '\Application\CMIS\InvoiceServiceValidator\STOM\Controllers\SimultaneousTeethInclusionFinderController::find');

#CMIS. ВАЛИДАЦИЯ РЕЕСТРОВ ПРИКРЕПЛЕНИЯ НАСЕЛЕНИЯ. ДИСПАНСЕРИЗАЦИЯ
//Ищет среди записей в реестре XML по диспансеризации людей, у которых не проставлено прикрепление в CMIS. Файлы D, F, L
$this->get('cmis/attachment/validate/disp', '\Application\CMIS\AttachmentValidator\Controllers\DispAttachmentValidatorController::validate');
//Ищет среди записей в реестре XML по стационару людей, у которых не проставлено прикрепление в CMIS. Файлы S, H, L
$this->get('cmis/attachment/validate/stac', '\Application\CMIS\AttachmentValidator\Controllers\StacAttachmentValidatorController::validate');

#РЕЕСТРЫ СЧЕТОВ. СТОМАТОЛОГИЯ. АНАЛИТИКА
//Поиск не корректных целей посещения 3.0/1.0
$this->get('invoices/analyzer/stom/incorrect-purposes', '\Application\Invoices\Analyzer\STOM\Controllers\IncorrectPurposeFinderController::find');
//Поиск не корректных услуг. Либо не одной первичной услуги в случае, либо 2 и более первичных услуг
$this->get('invoices/analyzer/stom/incorrect-services', '\Application\Invoices\Analyzer\STOM\Controllers\IncorrectServicesFinderController::find');
//Поиск некорректных диагнозов по отношению к пролеченным зубам
$this->get('invoices/analyzer/stom/incorrect-teeth', '\Application\Invoices\Analyzer\STOM\Controllers\IncorrectTeethFinderController::find');
//Поиск пересечений случаев за 30 дневный период
$this->get('invoices/analyzer/stom/intersections', '\Application\Invoices\Analyzer\STOM\Controllers\IntersectionsFinderController::find');
//Поиск разорванных случаев, когда на 1 пациента 2 и более случаев за 30 дней
$this->get('invoices/analyzer/stom/torn-cases', '\Application\Invoices\Analyzer\STOM\Controllers\TornCaseFinderController::find');