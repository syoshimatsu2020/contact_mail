<?php
    class OutputJson {
        private string $json;

        public function output($array) {
            header('Content-Type: application/json; charset=UTF-8');
            $this->json = json_encode($array, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
            if ($this->json !== false) {
                echo $this->json;
                return;
            }
        }
    }
?>