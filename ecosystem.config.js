module.exports = {
  apps: [
    {
      name: 'whatsapp-gateway',
      script: 'whatsapp-server.js',
      instances: 1,
      autorestart: true,
      watch: false,
      max_memory_restart: '1G',
      env: {
        NODE_ENV: 'production',
        WHATSAPP_PORT: 3001
      },
      env_production: {
        NODE_ENV: 'production',
        WHATSAPP_PORT: 3001
      },
      error_file: './logs/err.log',
      out_file: './logs/out.log',
      log_file: './logs/combined.log',
      time: true
    }
  ]
}; 