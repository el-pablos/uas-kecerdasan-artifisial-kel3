"""
========================================
Log Sentinel - Machine Learning Service
Sistem Deteksi Anomali menggunakan Isolation Forest
========================================

Microservice ini bertanggung jawab untuk:
1. Menerima data log server dari Laravel Backend
2. Melakukan preprocessing dan encoding data
3. Mendeteksi anomali menggunakan algoritma Isolation Forest
4. Mengembalikan hasil prediksi ke Laravel

Tim Pengembang:
- JEREMY CHRISTO EMMANUELLE PANJAITAN (237006516084)
- MUHAMMAD AKBAR HADI PRATAMA (237006516058)
- FARREL ALFARIDZI (237006516028)
- CHOSMAS LAURENS RUMNGEWUR (217006516074)
"""

from flask import Flask, request, jsonify
from flask_cors import CORS
from sklearn.ensemble import IsolationForest
from sklearn.preprocessing import LabelEncoder
import numpy as np
import pandas as pd
import joblib
import os
from datetime import datetime

# Inisialisasi aplikasi Flask
app = Flask(__name__)
CORS(app)  # Mengizinkan Cross-Origin Request dari Laravel

# ========================================
# KONFIGURASI MODEL MACHINE LEARNING
# ========================================

# Variabel global untuk menyimpan model dan encoder
model = None
label_encoders = {}

# Daftar HTTP methods yang dikenali sistem
HTTP_METHODS = ['GET', 'POST', 'PUT', 'DELETE', 'PATCH', 'HEAD', 'OPTIONS']

# Daftar User Agent yang umum (untuk encoding)
COMMON_USER_AGENTS = [
    'Mozilla/5.0',
    'Chrome',
    'Firefox',
    'Safari',
    'Edge',
    'Opera',
    'curl',
    'Postman',
    'Python-requests',
    'Unknown'
]


def generate_training_data():
    """
    Menghasilkan data training dummy untuk model Isolation Forest.
    Data ini merepresentasikan pola traffic normal pada server.
    
    Returns:
        DataFrame: Data training dengan kolom yang sesuai
    """
    np.random.seed(42)
    jumlah_sampel = 1000
    
    # Menghasilkan data traffic normal
    data_normal = {
        # IP Address dalam bentuk numerik (octet terakhir)
        'ip_numeric': np.random.randint(1, 255, jumlah_sampel),
        
        # HTTP Method - mayoritas GET dan POST untuk traffic normal
        'method': np.random.choice(['GET', 'POST', 'PUT', 'DELETE'], 
                                   jumlah_sampel, 
                                   p=[0.6, 0.25, 0.1, 0.05]),
        
        # Status Code - mayoritas 200 untuk traffic normal
        'status_code': np.random.choice([200, 201, 301, 302, 400, 404, 500], 
                                        jumlah_sampel, 
                                        p=[0.7, 0.1, 0.05, 0.05, 0.05, 0.03, 0.02]),
        
        # Response Time normal berkisar 50-500ms
        'response_time': np.random.uniform(50, 500, jumlah_sampel),
        
        # Panjang URL normal
        'url_length': np.random.randint(10, 100, jumlah_sampel),
        
        # User Agent index
        'user_agent_idx': np.random.randint(0, len(COMMON_USER_AGENTS) - 2, jumlah_sampel)
    }
    
    return pd.DataFrame(data_normal)


def initialize_model():
    """
    Menginisialisasi dan melatih model Isolation Forest.
    Model dilatih dengan data dummy normal saat startup.
    """
    global model, label_encoders
    
    print("[INFO] Memulai inisialisasi model Isolation Forest...")
    
    # Inisialisasi Label Encoders untuk data kategorikal
    label_encoders['method'] = LabelEncoder()
    label_encoders['method'].fit(HTTP_METHODS)
    
    # Generate data training
    data_training = generate_training_data()
    
    # Encode kolom method
    data_training['method_encoded'] = label_encoders['method'].transform(data_training['method'])
    
    # Siapkan fitur untuk training
    fitur_training = data_training[[
        'ip_numeric', 
        'method_encoded', 
        'status_code', 
        'response_time', 
        'url_length',
        'user_agent_idx'
    ]].values
    
    # Inisialisasi dan latih model Isolation Forest
    # contamination=0.1 berarti kita mengharapkan 10% data adalah anomali
    model = IsolationForest(
        n_estimators=100,          # Jumlah pohon dalam forest
        contamination=0.1,         # Proporsi outlier dalam data
        max_samples='auto',        # Jumlah sampel untuk melatih setiap pohon
        random_state=42,           # Untuk reproducibility
        n_jobs=-1                  # Gunakan semua CPU cores
    )
    
    # Latih model dengan data normal
    model.fit(fitur_training)
    
    print("[INFO] Model Isolation Forest berhasil diinisialisasi!")
    print(f"[INFO] Jumlah sampel training: {len(fitur_training)}")


def extract_ip_numeric(ip_address):
    """
    Mengekstrak nilai numerik dari IP address.
    Menggunakan hash dari IP untuk konsistensi.
    
    Args:
        ip_address: String IP address (misal: '192.168.1.1')
    
    Returns:
        int: Nilai numerik dari IP
    """
    try:
        # Ambil octet terakhir atau hash jika format tidak valid
        parts = ip_address.split('.')
        if len(parts) == 4:
            return int(parts[-1])
        return hash(ip_address) % 256
    except:
        return 0


def get_user_agent_index(user_agent):
    """
    Mendapatkan index User Agent berdasarkan pattern matching.
    
    Args:
        user_agent: String User Agent dari request
    
    Returns:
        int: Index User Agent
    """
    user_agent_lower = user_agent.lower() if user_agent else ''
    
    for idx, agent in enumerate(COMMON_USER_AGENTS):
        if agent.lower() in user_agent_lower:
            return idx
    
    return len(COMMON_USER_AGENTS) - 1  # Unknown


def preprocess_log_data(log_data):
    """
    Melakukan preprocessing pada data log untuk prediksi.
    
    Args:
        log_data: Dictionary berisi data log server
    
    Returns:
        numpy.array: Fitur yang siap untuk prediksi
    """
    # Ekstrak dan transform setiap fitur
    ip_numeric = extract_ip_numeric(log_data.get('ip_address', '0.0.0.0'))
    
    # Encode HTTP method
    method = log_data.get('method', 'GET').upper()
    if method not in HTTP_METHODS:
        method = 'GET'
    method_encoded = label_encoders['method'].transform([method])[0]
    
    # Status code
    status_code = int(log_data.get('status_code', 200))
    
    # Response time dalam milliseconds
    response_time = float(log_data.get('response_time', 100))
    
    # Panjang URL
    url = log_data.get('url', '/')
    url_length = len(url)
    
    # User Agent index
    user_agent = log_data.get('user_agent', 'Unknown')
    user_agent_idx = get_user_agent_index(user_agent)
    
    # Gabungkan semua fitur
    fitur = np.array([[
        ip_numeric,
        method_encoded,
        status_code,
        response_time,
        url_length,
        user_agent_idx
    ]])
    
    return fitur


def calculate_severity_score(log_data, prediction, anomaly_score):
    """
    Menghitung skor keparahan anomali berdasarkan beberapa faktor.
    
    Args:
        log_data: Data log original
        prediction: Hasil prediksi (-1 anomali, 1 normal)
        anomaly_score: Skor anomali dari model
    
    Returns:
        float: Skor keparahan (0-100)
    """
    if prediction == 1:
        return 0.0
    
    base_score = 50.0
    
    # Faktor status code
    status_code = int(log_data.get('status_code', 200))
    if status_code >= 500:
        base_score += 20
    elif status_code >= 400:
        base_score += 10
    
    # Faktor response time (lebih dari 2 detik = mencurigakan)
    response_time = float(log_data.get('response_time', 100))
    if response_time > 2000:
        base_score += 15
    elif response_time > 1000:
        base_score += 10
    
    # Faktor URL mencurigakan
    url = log_data.get('url', '').lower()
    suspicious_patterns = ['admin', 'login', 'wp-admin', 'phpmyadmin', 'shell', 'cmd', 'exec']
    for pattern in suspicious_patterns:
        if pattern in url:
            base_score += 5
    
    # Normalisasi ke range 0-100
    return min(100.0, base_score)


# ========================================
# ENDPOINT API
# ========================================

@app.route('/', methods=['GET'])
def index():
    """
    Endpoint root untuk health check.
    """
    return jsonify({
        'status': 'active',
        'service': 'Log Sentinel ML Service',
        'version': '1.0.0',
        'algorithm': 'Isolation Forest',
        'timestamp': datetime.now().isoformat()
    })


@app.route('/health', methods=['GET'])
def health_check():
    """
    Endpoint untuk memeriksa status kesehatan service.
    """
    model_status = 'ready' if model is not None else 'not_initialized'
    
    return jsonify({
        'status': 'healthy',
        'model_status': model_status,
        'timestamp': datetime.now().isoformat()
    })


@app.route('/predict', methods=['POST'])
def predict():
    """
    Endpoint utama untuk memprediksi anomali dari data log.
    
    Request Body (JSON):
        - ip_address: String IP address client
        - method: String HTTP method (GET, POST, dll)
        - url: String URL yang diakses
        - status_code: Integer HTTP status code
        - user_agent: String User Agent browser/client
        - response_time: Float waktu response dalam ms
    
    Response (JSON):
        - prediction: String 'normal' atau 'anomaly'
        - prediction_code: Integer (1=normal, -1=anomaly)
        - severity_score: Float skor keparahan (0-100)
        - confidence: Float tingkat kepercayaan prediksi
        - timestamp: String waktu prediksi
    """
    try:
        # Validasi request
        if not request.is_json:
            return jsonify({
                'error': 'Request harus dalam format JSON',
                'status': 'error'
            }), 400
        
        log_data = request.get_json()
        
        # Validasi field yang diperlukan
        required_fields = ['ip_address', 'method', 'url', 'status_code']
        for field in required_fields:
            if field not in log_data:
                return jsonify({
                    'error': f'Field {field} diperlukan',
                    'status': 'error'
                }), 400
        
        # Preprocessing data
        fitur = preprocess_log_data(log_data)
        
        # Prediksi menggunakan model
        prediction = model.predict(fitur)[0]
        
        # Dapatkan skor anomali (semakin negatif = semakin anomali)
        anomaly_score = model.decision_function(fitur)[0]
        
        # Hitung confidence (0-1)
        # Skor decision function berkisar dari -0.5 hingga 0.5
        confidence = abs(anomaly_score) * 2
        confidence = min(1.0, max(0.0, confidence))
        
        # Hitung severity score
        severity_score = calculate_severity_score(log_data, prediction, anomaly_score)
        
        # Tentukan label prediksi
        prediction_label = 'normal' if prediction == 1 else 'anomaly'
        
        # Buat response
        response = {
            'status': 'success',
            'data': {
                'prediction': prediction_label,
                'prediction_code': int(prediction),
                'severity_score': round(severity_score, 2),
                'confidence': round(confidence, 4),
                'anomaly_score': round(float(anomaly_score), 4),
                'timestamp': datetime.now().isoformat()
            },
            'input_data': {
                'ip_address': log_data.get('ip_address'),
                'method': log_data.get('method'),
                'url': log_data.get('url'),
                'status_code': log_data.get('status_code')
            }
        }
        
        # Log ke console untuk debugging
        status_icon = '✅' if prediction == 1 else '🚨'
        print(f"{status_icon} Prediksi: {prediction_label.upper()} | IP: {log_data.get('ip_address')} | URL: {log_data.get('url')}")
        
        return jsonify(response)
    
    except Exception as e:
        print(f"[ERROR] Terjadi kesalahan saat prediksi: {str(e)}")
        return jsonify({
            'status': 'error',
            'error': str(e),
            'timestamp': datetime.now().isoformat()
        }), 500


@app.route('/predict/batch', methods=['POST'])
def predict_batch():
    """
    Endpoint untuk memprediksi multiple log sekaligus.
    
    Request Body (JSON):
        - logs: Array of log objects
    
    Response (JSON):
        - results: Array of prediction results
        - summary: Object berisi ringkasan hasil
    """
    try:
        if not request.is_json:
            return jsonify({
                'error': 'Request harus dalam format JSON',
                'status': 'error'
            }), 400
        
        data = request.get_json()
        logs = data.get('logs', [])
        
        if not logs:
            return jsonify({
                'error': 'Array logs tidak boleh kosong',
                'status': 'error'
            }), 400
        
        results = []
        total_normal = 0
        total_anomaly = 0
        
        for log_data in logs:
            try:
                fitur = preprocess_log_data(log_data)
                prediction = model.predict(fitur)[0]
                anomaly_score = model.decision_function(fitur)[0]
                severity_score = calculate_severity_score(log_data, prediction, anomaly_score)
                
                prediction_label = 'normal' if prediction == 1 else 'anomaly'
                
                if prediction == 1:
                    total_normal += 1
                else:
                    total_anomaly += 1
                
                results.append({
                    'input': log_data,
                    'prediction': prediction_label,
                    'prediction_code': int(prediction),
                    'severity_score': round(severity_score, 2),
                    'anomaly_score': round(float(anomaly_score), 4)
                })
                
            except Exception as e:
                results.append({
                    'input': log_data,
                    'error': str(e)
                })
        
        return jsonify({
            'status': 'success',
            'results': results,
            'summary': {
                'total_processed': len(logs),
                'total_normal': total_normal,
                'total_anomaly': total_anomaly,
                'anomaly_rate': round(total_anomaly / len(logs) * 100, 2) if logs else 0
            },
            'timestamp': datetime.now().isoformat()
        })
    
    except Exception as e:
        return jsonify({
            'status': 'error',
            'error': str(e)
        }), 500


@app.route('/model/info', methods=['GET'])
def model_info():
    """
    Endpoint untuk mendapatkan informasi tentang model.
    """
    if model is None:
        return jsonify({
            'status': 'error',
            'error': 'Model belum diinisialisasi'
        }), 500
    
    return jsonify({
        'status': 'success',
        'model': {
            'algorithm': 'Isolation Forest',
            'n_estimators': model.n_estimators,
            'contamination': model.contamination,
            'max_samples': str(model.max_samples),
            'features': [
                'ip_numeric',
                'method_encoded', 
                'status_code',
                'response_time',
                'url_length',
                'user_agent_idx'
            ]
        },
        'timestamp': datetime.now().isoformat()
    })


# ========================================
# MAIN ENTRY POINT
# ========================================

if __name__ == '__main__':
    # Inisialisasi model saat startup
    initialize_model()
    
    # Konfigurasi server
    port = int(os.environ.get('ML_SERVICE_PORT', 5000))
    debug = os.environ.get('FLASK_DEBUG', 'False').lower() == 'true'
    
    print("=" * 50)
    print("  LOG SENTINEL - ML SERVICE")
    print("  Anomaly Detection System")
    print("=" * 50)
    print(f"  🚀 Server berjalan di http://127.0.0.1:{port}")
    print(f"  📊 Algorithm: Isolation Forest")
    print(f"  🔧 Debug Mode: {debug}")
    print("=" * 50)
    
    # Jalankan server Flask
    app.run(
        host='0.0.0.0',
        port=port,
        debug=debug
    )
