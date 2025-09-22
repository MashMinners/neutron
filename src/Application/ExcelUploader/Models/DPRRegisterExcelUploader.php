<?php

declare(strict_types=1);

namespace Application\ExcelUploader\Models;

class DPRRegisterExcelUploader extends BaseRegisterExcelUploader
{
    protected $_table = 'dpr_register';
    protected $_unique_entry = 'dpr_register_unique_entry';
    protected $_register_patient = 'dpr_register_patient';
    protected $_register_patient_date_birth = 'dpr_register_patient_date_birth';
    protected $_register_patient_insurance_policy = 'dpr_register_patient_insurance_policy';
    protected $_register_treatment_start = 'dpr_register_treatment_start';
    protected $_register_treatment_end = 'dpr_register_treatment_end';
    protected $_register_diagnosis = 'dpr_register_diagnosis';
    protected $_register_doctor = 'dpr_register_doctor';

}