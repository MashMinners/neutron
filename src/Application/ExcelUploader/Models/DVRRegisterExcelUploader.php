<?php

declare(strict_types=1);

namespace Application\ExcelUploader\Models;

class DVRRegisterExcelUploader extends BaseRegisterExcelUploader
{
    protected $_table = 'dvr_register';
    protected $_unique_entry = 'dvr_register_unique_entry';
    protected $_register_patient = 'dvr_register_patient';
    protected $_register_patient_date_birth = 'dvr_register_patient_date_birth';
    protected $_register_patient_insurance_policy = 'dvr_register_patient_insurance_policy';
    protected $_register_treatment_start = 'dvr_register_treatment_start';
    protected $_register_treatment_end = 'dvr_register_treatment_end';
    protected $_register_diagnosis = 'dvr_register_diagnosis';
    protected $_register_doctor = 'dvr_register_doctor';

}