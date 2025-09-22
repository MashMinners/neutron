<?php

declare(strict_types=1);

namespace Application\ExcelUploader\Models;

use Engine\Database\IConnector;
use PhpOffice\PhpSpreadsheet\IOFactory;

/**
 * Загрузка в БД записи по ПЕРВОМУ ЭТАПУ ДИСПАНСЕРИЗАЦИИ
 */
class DPRegisterExcelUploader extends BaseRegisterExcelUploader
{
    protected $_table = 'dp_register';
    protected $_unique_entry = 'dp_register_unique_entry';
    protected $_register_patient = 'dp_register_patient';
    protected $_register_patient_date_birth = 'dp_register_patient_date_birth';
    protected $_register_patient_insurance_policy = 'dp_register_patient_insurance_policy';
    protected $_register_treatment_start = 'dp_register_treatment_start';
    protected $_register_treatment_end = 'dp_register_treatment_end';
    protected $_register_diagnosis = 'dp_register_diagnosis';
    protected $_register_doctor = 'dp_register_doctor';

}