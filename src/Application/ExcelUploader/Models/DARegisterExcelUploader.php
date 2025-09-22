<?php

declare(strict_types=1);

namespace Application\ExcelUploader\Models;

class DARegisterExcelUploader extends BaseRegisterExcelUploader
{
    protected $_table = 'da_register';
    protected $_unique_entry = 'da_register_unique_entry';
    protected $_register_patient = 'da_register_patient';
    protected $_register_patient_date_birth = 'da_register_patient_date_birth';
    protected $_register_patient_insurance_policy = 'da_register_patient_insurance_policy';
    protected $_register_treatment_start = 'da_register_treatment_start';
    protected $_register_treatment_end = 'da_register_treatment_end';
    protected $_register_diagnosis = 'da_register_diagnosis';
    protected $_register_doctor = 'da_register_doctor';

}