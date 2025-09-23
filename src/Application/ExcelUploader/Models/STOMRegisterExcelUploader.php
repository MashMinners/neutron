<?php

declare(strict_types=1);

namespace Application\ExcelUploader\Models;

class STOMRegisterExcelUploader extends BaseRegisterExcelUploader
{
    protected $_table = 'stom_register';
    protected $_unique_entry = 'stom_register_unique_entry';
    protected $_register_patient = 'stom_register_patient';
    protected $_register_patient_date_birth = 'stom_register_patient_date_birth';
    protected $_register_patient_insurance_policy = 'stom_register_patient_insurance_policy';
    protected $_register_treatment_start = 'stom_register_treatment_start';
    protected $_register_treatment_end = 'stom_register_treatment_end';
    protected $_register_diagnosis = 'stom_register_diagnosis';
    protected $_register_doctor = 'stom_register_doctor';

}