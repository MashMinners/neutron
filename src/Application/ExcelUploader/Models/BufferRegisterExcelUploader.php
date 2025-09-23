<?php

declare(strict_types=1);

namespace Application\ExcelUploader\Models;

class BufferRegisterExcelUploader extends BaseRegisterExcelUploader
{
    protected $_table = 'buffer_register';
    protected $_unique_entry = 'buffer_register_unique_entry';
    protected $_register_patient = 'buffer_register_patient';
    protected $_register_patient_date_birth = 'buffer_register_patient_date_birth';
    protected $_register_patient_insurance_policy = 'buffer_register_patient_insurance_policy';
    protected $_register_treatment_start = 'buffer_register_treatment_start';
    protected $_register_treatment_end = 'buffer_register_treatment_end';
    protected $_register_diagnosis = 'buffer_register_diagnosis';
    protected $_register_doctor = 'buffer_register_doctor';


}