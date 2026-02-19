# UZ Exchange Rate Monitor (Telegram Bot)
UZ Exchange rate API integration to telegram notifications

A lightweight, native PHP script that monitors exchange rates from the Central Bank of Uzbekistan (CBU) and sends smart notifications to Telegram based on custom triggers.

## Features
- **Zero Frameworks:** Pure PHP for maximum performance.
- **Smart Triggers:** Notifies on price spikes, percentage changes, or threshold crossings.
- **Historical Data:** Tracks rates in a JSON-based local storage (configurable history depth).
- **Secure:** Uses clean code practices and cURL for API requests.

## Installation
1. Clone the repository:
   ```bash
   git clone [https://github.com/yourusername/exchange_rate_telegram.git](https://github.com/yourusername/exchange_rate_telegram.git)

2. Set your credentials in config.php.

3. Set up a Cron Job to run the script every hour:
0 * * * * php /path/to/project/process.php

**Requirements**
PHP 7.4 or higher
JSON & cURL extensions

**License**
This project is licensed under the MIT License.
