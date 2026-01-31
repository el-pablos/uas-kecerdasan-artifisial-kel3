module.exports = {
  apps: [{
    name: 'ml-service',
    script: '/root/work/uas-kecerdasan-artifisial-kel3/ml_service/venv/bin/gunicorn',
    args: '-w 2 -b 0.0.0.0:5000 app:app',
    cwd: '/root/work/uas-kecerdasan-artifisial-kel3/ml_service',
    interpreter: 'none',
    env: {
      PATH: '/root/work/uas-kecerdasan-artifisial-kel3/ml_service/venv/bin:' + process.env.PATH
    }
  }]
}
