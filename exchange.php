<?php
class ExchangeMonitor {
    private $config;

    public function __construct($config) {
        $this->config = $config;
    }

    // Пример получения данных (используем CBU UZ API)
    public function fetchRates(): array {
        $ch = curl_init("https://cbu.uz/ru/arkhiv-kursov-valyut/json/");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);
        return json_decode($response, true) ?? [];
    }

    public function process() {
        $rates = $this->fetchRates();
        $history = $this->loadHistory();
        
        foreach ($rates as $rate) {
            if ($rate['Ccy'] === 'USD') {
                $currentPrice = (float)$rate['Rate'];
                $this->checkTriggers($currentPrice, $history['USD'] ?? []);
                $this->updateHistory('USD', $currentPrice, $history);
            }
        }
    }

    private function checkTriggers($current, $pastData) {
        if (empty($pastData)) return;

        $last = end($pastData)['rate'];
        $diff = (($current - $last) / $last) * 100;

        if (abs($diff) >= $this->config['thresholds']['USD']['percent_change']) {
            $msg = ($diff > 0 ? "🚀 " : "🔻 ") . "USD Rate changed by " . round($diff, 2) . "% ($current UZS)";
            $this->sendTelegram($msg);
        }
    }

    private function sendTelegram($text) {
        $url = "https://api.telegram.org/bot{$this->config['telegram_token']}/sendMessage";
        $data = ['chat_id' => $this->config['chat_id'], 'text' => $text];
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_exec($ch);
        curl_close($ch);
    }

    // Методы loadHistory и updateHistory для работы с JSON...
}
