<?php

class ExchangeMonitor {
    private $config;
    private $error;

    public function __construct($config) {
        $this->config = $config;
        // Создаем папку для данных, если её нет
        if (!file_exists(dirname($this->config['data_file']))) {
            mkdir(dirname($this->config['data_file']), 0777, true);
        }
    }

    /**
     * Основной цикл работы
     */
    public function process() {
        $rates = $this->fetchRates();
        if (empty($rates)) return;

        $history = $this->loadHistory();
        $updated = false;

        $report = [];

        foreach ($rates as $rate) {
            // Мониторим только те валюты, что есть в конфиге
            $ccy = $rate['Ccy']; // Например, 'USD'
            if (isset($this->config['thresholds'][$ccy])) {
                $currentPrice = (float)$rate['Rate'];

                // 1. Проверяем триггеры
                $data = $this->checkTriggers($ccy, $currentPrice, $history[$ccy] ?? []);

                if(is_array($data)) {
                    $report[] = implode("\n", $data);
                }
                // 2. Обновляем историю
                $this->updateHistory($ccy, $currentPrice, $history);
                $updated = true;
            }
        }

        if(count($report) > 0) {
            // Если есть что сказать — отправляем
            $result = $this->sendTelegram(implode("\n\n", $report));
            if(!$result['ok']) {
                $this->error = $result['description'];
            }
        }

        if ($updated) {
            $this->saveHistory($history);
        }
    }

    private function fetchRates(): array {
        $ch = curl_init("https://cbu.uz/ru/arkhiv-kursov-valyut/json/");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        $response = curl_exec($ch);
        curl_close($ch);
        return json_decode($response, true) ?? [];
    }

    public function error() {
        if(!$this->error) {
            return true;
        }

        return $this->error;
    }

    private function checkTriggers($ccy, $current, $pastData) {
        if (empty($pastData)) return false;

        $lastRecord = end($pastData);
        $lastPrice = $lastRecord['rate'];
        
        // Разница в процентах
        $diffPercent = (($current - $lastPrice) / $lastPrice) * 100;
        $threshold = $this->config['thresholds'][$ccy];

        $report = [];
        
        // Проверка на резкий скачок (%)
        if (abs($diffPercent) >= $threshold['percent_change']) {
            $emoji = $diffPercent > 0 ? "📈" : "📉";
            $plus = $diffPercent > 0 ? "+" : "-";
            $report[] = htmlspecialchars("{$emoji} Изменение курса: {$plus}" . round($diffPercent, 2) . "%");
        }

        // Проверка на выход за границы (min/max)
        if ($current > $threshold['max']) {
            $report[] = htmlspecialchars("⚠️ Выше лимита: {$threshold['max']}");
        } elseif ($current < $threshold['min']) {
            $report[] = htmlspecialchars("🔔 Ниже лимита: {$threshold['min']}");
        }

        if(count($report) > 0) {
            array_unshift($report, "<b>{$ccy}</b> = <code>{$current}</code>UZS");
        }

        return $report;
    }

    private function loadHistory(): array {
        if (!file_exists($this->config['data_file'])) return [];
        $data = file_get_contents($this->config['data_file']);
        return json_decode($data, true) ?? [];
    }

    private function saveHistory(array $history) {
        file_put_contents($this->config['data_file'], json_encode($history, JSON_PRETTY_PRINT));
    }

    private function updateHistory($ccy, $price, &$history) {
        $now = date('Y-m-d H:i:s');
        
        if (!isset($history[$ccy])) {
            $history[$ccy] = [];
        }

        // Добавляем новую запись
        $history[$ccy][] = [
            'date' => $now,
            'rate' => $price
        ];

        // Очищаем старые записи (храним только N дней)
        // Для простоты считаем количество запусков (если раз в час, то 24 * дни)
        $maxRecords = 24 * $this->config['history_days']; 
        if (count($history[$ccy]) > $maxRecords) {
            array_shift($history[$ccy]);
        }
    }

    private function sendTelegram($text) {

        $url = "https://api.telegram.org/bot{$this->config['telegram_token']}/sendMessage";
        $postData = [
            'chat_id' => $this->config['chat_id'],
            'text' => $text,
            'parse_mode' => 'HTML'
        ];
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        $result = curl_exec($ch);
        curl_close($ch);

        $json = json_decode($result, true);

        return $json ?? ['ok' => false, 'description' => 'JSON parse error'];
    }
}
