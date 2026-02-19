# UZ Exchange Rate Monitor (Telegram Bot)
A lightweight, native PHP script that monitors exchange rates from the Central Bank of Uzbekistan (CBU) and sends smart notifications to Telegram based on custom triggers.

## Features
+ Zero Frameworks: Pure PHP for maximum performance.
+ Smart Triggers: Notifies on price spikes, percentage changes, or threshold crossings.
+ Historical Data: Tracks rates in a JSON-based local storage (configurable history depth).
+ Secure: Uses clean code practices and cURL for API requests.

## Installation
1. Clone the repository:
```
git clone https://github.com/Deorgss/exchange_rate_telegram.git
```

2. Copy the example config:
```
cp config.php.example config.php
```

3. fill in your credentials

## Running directly
Set up a Cron Job to run the script every hour:
```
0 * * * * php /path/to/project/process.php
```

## Running with Docker
The easiest way to run the monitor is using Docker Compose. It includes a built-in scheduler (Ofelia), so you don't need to configure your system crontab.
Start the containers:
```
docker-compose up -d
```


## Requirements
+ PHP 7.4 or higher
+ PHP JSON & cURL extensions

**License**
This project is licensed under the MIT License.
