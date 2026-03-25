"campos":   {
  "name": "discolnet/PisciWeb",
  "description": "PisciWeb - Arquitectura con namespaces optimizada",
  "autoload": {
    "psr-4": {
      "App\\": "app/",
      "Modules\\": "app/modules/",
      "NotificationWebSocket\\": "WebSocket/"
    }
  },
  "require": {
    "php": ">=7.4",
    "dompdf/dompdf": "^3.1",
    "box/spout": "^3.3",
    "phpoffice/phpspreadsheet": "^1.29",
    "phpmailer/phpmailer": "^7.0",
    "ratchet/pawl": "^0.4",
    "ratchet/rfc6455": "^0.3",
    "react/socket": "^1.12",
    "react/http": "^1.7",
    "react/stream": "^1.2",
    "react/promise": "^3.0",
    "evenement/evenement": "^3.0"
  },
  "scripts": {
    "websocket:start": "php WebSocket/start_server.php",
    "websocket:start-ssl": "php WebSocket/start_server_ssl.php"
  },
  "config": {
    "optimize-autoloader": true
  }
}
