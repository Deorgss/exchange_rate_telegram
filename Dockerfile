# Используем легковесный образ PHP 8.2 на базе Alpine
FROM php:8.2-cli-alpine

# Устанавливаем системные зависимости для работы cURL
RUN apk add --no-cache curl-dev libxml2-dev \
    && docker-php-ext-install curl

# Создаем рабочую директорию
WORKDIR /app

# Копируем файлы проекта
COPY . .

# Создаем папку для логов/данных и даем права на запись
RUN mkdir -p data && chmod -R 777 data

# Запускаем скрипт (через цикл или просто один раз)
# Для работы как сервис можно использовать:
CMD ["php", "./process.php"]
