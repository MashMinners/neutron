<?php

declare(strict_types=1);

namespace Application\ExcelUploader\Models;

class DORegisterExcelUploader extends BaseRegisterExcelUploader
{
    protected $_table = 'do_register';
    protected $_unique_entry = 'do_register_unique_entry';
    protected $_register_patient = 'do_register_patient';
    protected $_register_patient_date_birth = 'do_register_patient_date_birth';
    protected $_register_patient_insurance_policy = 'do_register_patient_insurance_policy';
    protected $_register_treatment_start = 'do_register_treatment_start';
    protected $_register_treatment_end = 'do_register_treatment_end';
    protected $_register_diagnosis = 'do_register_diagnosis';
    protected $_register_doctor = 'do_register_doctor';

}