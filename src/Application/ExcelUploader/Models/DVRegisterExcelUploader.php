<?php

declare(strict_types=1);

namespace Application\ExcelUploader\Models;

class DVRegisterExcelUploader extends BaseRegisterExcelUploader
{
    protected $_table = 'dv_register';
    protected $_unique_entry = 'dv_register_unique_entry';
    protected $_register_patient = 'dv_register_patient';
    protected $_register_patient_date_birth = 'dv_register_patient_date_birth';
    protected $_register_patient_insurance_policy = 'dv_register_patient_insurance_policy';
    protected $_register_treatment_start = 'dv_register_treatment_start';
    protected $_register_treatment_end = 'dv_register_treatment_end';
    protected $_register_diagnosis = 'dv_register_diagnosis';
    protected $_register_doctor = 'dv_register_doctor';

}