# Конвертер валют на Laravel

## Структура проекта
Ниже кратко описана структура директорий
```
└── app/                  # основная папка с проектом
    └── Console/          # папка с настройкой консольных команды
        └── Commands/     # консольные команды
    └── Contracts/        # папка с интерфейсами
    └── Exceptions/       # папка с кастомными ошибками
    └── Http/             # web папка
        └── Controllers/  # файлы контроллеров
        └── Middleware/   # файлы middleware
    └── Models/           # папка с моделями (не содержит файлов)
    └── Providers/        # сервис провайдеры
    └── Rules/            # папка с кастомными валидаторами
    └── Services/         # сервисный слой проекта
        └── External/     # внешние сервисы
    └── Validations/      # папка с сервисами валидации
        └── DTOs/         # Data Transfer Objects
└── bootstrap/            # системная папка для автозагрузки
└── config/               # папка с основными конфигами проекта
└── database/             # папка с общими моделями, помощниками и т.п. 
    └── factories/        # папка с фабриками для быстрой генерации моделей
    └── migrations/       # папка с миграциями
    └── seeders/          # папка с фикстурами данных
└── lang/                 # файлы локализации
└── public/               # публичная папка (точка входа для web) 
└── resources/            # рерурсный слой проекта
    └── css/              # файлы стилей
    └── js/               # javascript файлы
    └── views/            # папка с представлениями проекта
└── routes/               # роутинг проекта
└── storage/              # хранилище данных фреймворка
└── tests/                # папка с тестами 
└── vendor/               # папка в внешними зависимостями
├── .env.example          # пример конфигурационного файла
├── .gitignore                
├── docker-compose.yml    # конфигурация для docker-compose
├── codeception.yml       # конфиг Codeception
└── composer.json         # основные зависимости
```

## Разворачивание проекта
* сделайте git clone проекта
* задайте все необходимые переменные окружения в .env файле 
* выполните установку зависимостей через composer install
* либо, запустите сборку контейнеров проекта через docker-compose up -d.
 После сборки можно выполнять команды как из среды контейнера через
   ```docker exec -it {container_name} sh```
 так и через интерактивную оболочку, например
   ```docker exec {container_name} php artisan ide-helper:generate`
* либо, если на вашем сервере установлен php 8.1 + redis, то можно обойтись прямым запуском команд

## Описание существующих консольных команд
Пользователю доступны 2 команды:
* php artisan rates:cache
* php artisan currency:convert {amount} {from} {to}

Первая команда лишь выполняет поиск данных по источникам и записывает их в кэш. Эту команду удобно ставить на запуск, например ежедневно в 00:00, чтобы она парсила обновленные данные по валютам и в дальнейшем конвертеру не приходилось делать лишние запросы. Однако, по желанию кэш можно отключить через конфиг.

Вторая команда выполняет непосредственно конвертацию. Необходимо передать 3 параметра: сумма, валюта "из" и валюта "в". Допускается только передача данных о валюте в формате ISO 4217. Кроме того, доступны лишь те валюты, что есть в имеющихся источниках, однако список легко расширять, добавляя новые источники (см.ниже). Сумму допускается передавать только больше нуля с не более чем двумя цифрами после запятой. Результат выдается в форме 
* ```{сумма} {валюта} = {результат} {валюта}```, 
например 
* ``` 1 BTC = 41234.5435 USD ```

## Описание алгоритма работы конвертера
* Первоначально, происходит валидация входных параметров, за это отвечает ExchangerValidator.php и встроенный валидатор Laravel. После, данные складываются в соответствующее DTO.
* Далее, в работу включается сервис по запросу курсов валют RatesService.php. Его задача заключается в том, чтобы получить итоговый массив с курсами валют. Для этого он выполняет несколько задач. Первоначально, запрашивает данные из кэша и при наличии возвращает их, при отсутствии он запрашивает каждый сервис валют. После получения данных данные форматируются в единый вид.
* Внешние сервисы выделены в отдельную папку и решают задачу получения данных от стороннего API и преобразования в единый формат. Задачи валидации и унификации формата данных решает ExchangerValidator.php. Сервисы способны принимать данные лишь 2-х типов: JSON и XML, в дальнейшем можно будет определить метод toArray для описания алгоритма получения массива данных. Добавление сервисов на данный момент осуществляется через конфигурационный файл (по-хорошему, можно обойтись без него, подключив рефлексию, но с точки зрения читабельности кода так лучше) и создание самого класса-сервиса.
* Сервис конвертера валют решает базовую задачу по преобразованию валют. Принцип конвертации заклчается в том, что если в массиве курсов имеется возможность прямой конвертации сервис выполняет её, если такой возможности нет, то сервис приводит все валюты, не имеющей конвертации в базовой валюте (в данном случае, евро, но можно изменить через конфигурацию) к единому виду, чтобы иметь возможность через евро унифицировано произвести конвертацию. 
* В конечном счете, полученное значение валидируется и выводится на экран

## Автотестирование
* в разработке
