# bugtracker_vk
Получение карточки тестировщика

# Ссылки
* [https://clefer.ru/bugtracker/?id=138269465](https://clefer.ru/bugtracker/?id=138269465) — пример использования

# Используемые библиотеки
* simple_html_dom

## Параметры

| Параметры    | Тип           | Описание              |
|--------------|---------------|-----------------------|
| id           | int           | ID пользователя       |

## Пример ответа
```
{
    "response": {
        "id": 138269465,
        "first_name": "Максим",
        "last_name": "Смирнов",
        "status": 5,
        "description": "Участвует в программе VK Testers",
        "reports": 144,
        "products": {
            "count": 181,
            "items": [
                {
                    "id": 169,
                    "name": "Мои желания",
                    "reports": 4
                },
                {
                    "id": 326,
                    "name": "Каталог Франшиз (Каталог)",
                    "reports": 4
                },
                {
                    "id": 24,
                    "name": "VK.com",
                    "reports": 3
                },
                ...
                {
                    "name": "40 секретных продуктов",
                    "reports": 85
                }
            ]
        }
    }
}
```
