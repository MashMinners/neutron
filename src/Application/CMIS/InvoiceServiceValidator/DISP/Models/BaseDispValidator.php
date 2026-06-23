<?php

namespace Application\CMIS\InvoiceServiceValidator\DISP\Models;

use DateTime;
use InvalidArgumentException;

class BaseDispValidator
{
    protected function getAgeInCurrentYear($birthDate){
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

}