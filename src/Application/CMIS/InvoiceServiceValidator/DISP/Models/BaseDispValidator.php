<?php

namespace Application\CMIS\InvoiceServiceValidator\DISP\Models;

use DateTime;
use InvalidArgumentException;

class BaseDispValidator
{
    private array $samples = [
        'DP' => 'dp_sample',
        'DA' => 'da_sample'
    ];

    //Данная функция определяет возвраст на текущую дату
    protected function getAgeInCurrentYearByCurrentDate($birthDate){
        // Парсим дату рождения
        $birth = DateTime::createFromFormat('Y-m-d', $birthDate);
        if (!$birth) {
            throw new InvalidArgumentException('Неверный формат даты. Используйте ГГГГ-ММ-ДД');
        }
        // Текущая дата
        $now = new DateTime();

        // Вычисляем возраст
        $age = $now->format('Y') - $birth->format('Y');

        // Проверяем, был ли уже день рождения в этом году
        $birthdayThisYear = new DateTime($now->format('Y') . '-' . $birth->format('m-d'));

        if ($now < $birthdayThisYear) {
            $age--; // Если день рождения ещё не наступил, уменьшаем возраст на 1
        }

        return $age;
    }

    //Данная функция определяет сколько лет пациенту будет в текущем году
    protected function getAgeInCurrentYear(string $birthDate){
        // Парсим дату рождения
        $birth = DateTime::createFromFormat('Y-m-d', $birthDate);
        if (!$birth) {
            return null; // Неверный формат
        }

        $currentYear = (int) date('Y');
        $birthYear = (int) $birth->format('Y');

        // Возраст в текущем календарном году = текущий год - год рождения
        // Потому что день рождения в этом году обязательно наступит
        return $currentYear - $birthYear;
    }

    protected function getSample(string $type) : array{
        $sample = include 'storage/cmis/samples/'.$this->samples[$type].'.php';
        return $sample;
    }

    protected function getUslGroupedByIdPac(array $file){
        foreach ($file['ZAP'] AS $key => $value){
            $idPac =$value['PACIENT'][0]['ID_PAC'];
            foreach ($value['Z_SL'][0]['SL'][0]['USL'] AS $key => $value){
                $data[$idPac][] = $value['CODE_USL'];
            }
        }
        return $data;
    }

    protected function getPersGroupedByIdPac(array $file){
        foreach ($file['PERS'] AS $key => $value){
            $result[$value['ID_PAC']] = $value;
        }
        return $result;
    }

    protected function getPersons(array $validationResult){
        /*$validatedPersons = [];
        foreach ($validationResult AS $id => $usl){
            if (!empty($usl)){
                $validatedPersons[$id]['PERS'] = $pers[$id];
                $validatedPersons[$id]['USL'] = $usl;
            }
        }
        return $validatedPersons;*/
        $validatedPersons = [];
        foreach ($validationResult AS $id => $result){
            if (!empty($result['USL'])){
                $validatedPersons[$id] = $result;
            }
        }
        return $validatedPersons;
    }

}