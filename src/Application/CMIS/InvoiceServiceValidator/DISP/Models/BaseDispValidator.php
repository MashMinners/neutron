<?php

namespace Application\CMIS\InvoiceServiceValidator\DISP\Models;

use DateTime;
use InvalidArgumentException;

class BaseDispValidator
{
    private array $samples = [
        'DP' => 'dp_sample'
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

}