# Temp_Stirage_System_backend
Пример работы системы хранения данных для открытой системы (https://github.com/ArtRu-ai/ESP8266-Temperature-measurement-open-system и https://github.com/alexandr-blinkov/vue-ymaps)
# Описание
# Компоненты открытой системы хранения данных
Для запуска системы использовались следущие элементы:
* Сервер Nginx
* MariaDB
* PHP-FPM
* Ubuntu Server
# Основные требования к работе с открытой системой
* Общий вид БД (https://github.com/grinzya/Temp_Stirage_System_backend/blob/main/base.sql)
* Для доступа системы сбора данных к системе хранения данных в базе данных должен быть прописан логин и пароль конкретной системы сбора данных.
# Протокол взаимодействия системы сбора данных - системы хранения данных - системы визуализации
Разработан PHP скрипт для обработки POST запросов от устройств сбора данных, а также обработки GET и OPTIONS запросов от системы визуализации. Данный скрипт позволяет взимодействовать всем частям отрытой системы. (https://github.com/grinzya/Temp_Stirage_System_backend/blob/main/records.php)
