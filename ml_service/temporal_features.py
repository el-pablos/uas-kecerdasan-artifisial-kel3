"""
========================================
TEMPORAL SLIDING WINDOW MODULE
Modul untuk Feature Engineering berbasis Waktu
========================================

Paper Reference:
"Behavioral Context-aware Anomaly Detection using Temporal Sliding Window"

Modul ini mengubah deteksi berbasis titik (point-based) menjadi 
deteksi berbasis perilaku (behavioral context) yang sangat krusial
untuk mendeteksi DDoS, Brute Force, dan serangan modern lainnya.

========================================
Lead Researcher & Developer (Journal-Grade Overhaul):
  MUHAMMAD AKBAR HADI PRATAMA
  GitHub: @el-pablos
  Email: yeteprem.end23juni@gmail.com

Original Contributors / Legacy Team:
  - Jeremy Christo Emmanuelle Panjaitan (237006516084)
  - Farrel Alfaridzi (237006516028)
  - Chosmas Laurens Rumngewur (217006516074)
========================================
"

import pandas as pd
import numpy as np
from datetime import datetime, timedelta
from collections import deque
from typing import Dict, List, Optional, Tuple
import threading


class TemporalSlidingWindow:
    """
    Kelas untuk mengelola sliding window dan menghitung fitur temporal.
    
    Fitur yang dihitung:
    1. req_count_1min: Jumlah request dalam 1 menit terakhir
    2. req_count_5min: Jumlah request dalam 5 menit terakhir
    3. avg_response_time_1min: Rata-rata response time 1 menit
    4. avg_bytes_5min: Rata-rata ukuran payload dalam 5 menit
    5. error_rate_1min: Persentase error (4xx, 5xx) dalam 1 menit
    6. error_rate_slope: Tren kenaikan error rate (derivative)
    7. unique_urls_1min: Jumlah URL unik (deteksi scanning)
    8. method_entropy: Entropi distribusi HTTP method (deteksi abnormal pattern)
    """
    
    def __init__(self, window_size_minutes: int = 10):
        """
        Inisialisasi sliding window.
        
        Args:
            window_size_minutes: Ukuran maksimal window dalam menit
        """
        self.window_size = timedelta(minutes=window_size_minutes)
        self.log_buffer: deque = deque()
        self.lock = threading.Lock()
        
        # Konfigurasi time windows
        self.windows = {
            '1min': timedelta(minutes=1),
            '5min': timedelta(minutes=5),
            '10min': timedelta(minutes=10)
        }
        
        print(f"[INFO] TemporalSlidingWindow initialized (window: {window_size_minutes} min)")
    
    def add_log(self, log_data: Dict) -> None:
        """
        Menambahkan log baru ke buffer dengan timestamp.
        
        Args:
            log_data: Dictionary berisi data log server
        """
        with self.lock:
            # Tambahkan timestamp jika belum ada
            if 'timestamp' not in log_data:
                log_data['timestamp'] = datetime.now()
            elif isinstance(log_data['timestamp'], str):
                log_data['timestamp'] = datetime.fromisoformat(log_data['timestamp'])
            
            self.log_buffer.append(log_data)
            
            # Bersihkan log yang sudah expired
            self._cleanup_expired_logs()
    
    def _cleanup_expired_logs(self) -> None:
        """
        Menghapus log yang sudah melewati window maksimal.
        """
        cutoff_time = datetime.now() - self.window_size
        
        while self.log_buffer and self.log_buffer[0]['timestamp'] < cutoff_time:
            self.log_buffer.popleft()
    
    def get_logs_in_window(self, window_key: str = '1min') -> List[Dict]:
        """
        Mengambil semua log dalam window tertentu.
        
        Args:
            window_key: Key dari window ('1min', '5min', '10min')
        
        Returns:
            List log yang masih dalam window
        """
        with self.lock:
            cutoff_time = datetime.now() - self.windows.get(window_key, self.windows['1min'])
            return [log for log in self.log_buffer if log['timestamp'] >= cutoff_time]
    
    def calculate_request_count(self, ip_address: str, window_key: str = '1min') -> int:
        """
        Menghitung jumlah request dari IP tertentu dalam window.
        
        Args:
            ip_address: IP address yang akan dihitung
            window_key: Window waktu
        
        Returns:
            Jumlah request
        """
        logs = self.get_logs_in_window(window_key)
        return sum(1 for log in logs if log.get('ip_address') == ip_address)
    
    def calculate_avg_response_time(self, ip_address: str = None, window_key: str = '1min') -> float:
        """
        Menghitung rata-rata response time dalam window.
        
        Args:
            ip_address: Filter by IP (None = semua IP)
            window_key: Window waktu
        
        Returns:
            Rata-rata response time dalam ms
        """
        logs = self.get_logs_in_window(window_key)
        
        if ip_address:
            logs = [log for log in logs if log.get('ip_address') == ip_address]
        
        if not logs:
            return 0.0
        
        response_times = [float(log.get('response_time', 0)) for log in logs]
        return np.mean(response_times) if response_times else 0.0
    
    def calculate_avg_bytes(self, ip_address: str = None, window_key: str = '5min') -> float:
        """
        Menghitung rata-rata ukuran request/response dalam window.
        
        Args:
            ip_address: Filter by IP (None = semua IP)
            window_key: Window waktu
        
        Returns:
            Rata-rata bytes
        """
        logs = self.get_logs_in_window(window_key)
        
        if ip_address:
            logs = [log for log in logs if log.get('ip_address') == ip_address]
        
        if not logs:
            return 0.0
        
        # Estimasi bytes dari panjang URL + user agent
        bytes_list = []
        for log in logs:
            url_len = len(log.get('url', ''))
            ua_len = len(log.get('user_agent', ''))
            # Estimasi sederhana: URL + headers
            estimated_bytes = url_len + ua_len + 200  # 200 untuk HTTP headers dasar
            bytes_list.append(estimated_bytes)
        
        return np.mean(bytes_list) if bytes_list else 0.0
    
    def calculate_error_rate(self, ip_address: str = None, window_key: str = '1min') -> float:
        """
        Menghitung persentase error (4xx, 5xx) dalam window.
        
        Args:
            ip_address: Filter by IP (None = semua IP)
            window_key: Window waktu
        
        Returns:
            Error rate (0.0 - 1.0)
        """
        logs = self.get_logs_in_window(window_key)
        
        if ip_address:
            logs = [log for log in logs if log.get('ip_address') == ip_address]
        
        if not logs:
            return 0.0
        
        error_count = sum(1 for log in logs if int(log.get('status_code', 200)) >= 400)
        return error_count / len(logs)
    
    def calculate_error_rate_slope(self, ip_address: str = None) -> float:
        """
        Menghitung tren kenaikan error rate (derivative).
        Membandingkan error rate 1 menit terakhir vs 5 menit sebelumnya.
        
        Args:
            ip_address: Filter by IP (None = semua IP)
        
        Returns:
            Slope (positif = trending up, negatif = trending down)
        """
        error_rate_1min = self.calculate_error_rate(ip_address, '1min')
        error_rate_5min = self.calculate_error_rate(ip_address, '5min')
        
        # Slope: perubahan error rate
        # Nilai positif berarti error rate naik (berbahaya)
        return error_rate_1min - error_rate_5min
    
    def calculate_unique_urls(self, ip_address: str, window_key: str = '1min') -> int:
        """
        Menghitung jumlah URL unik yang diakses oleh IP tertentu.
        Berguna untuk mendeteksi scanning/enumeration.
        
        Args:
            ip_address: IP address yang akan dihitung
            window_key: Window waktu
        
        Returns:
            Jumlah URL unik
        """
        logs = self.get_logs_in_window(window_key)
        logs = [log for log in logs if log.get('ip_address') == ip_address]
        
        unique_urls = set(log.get('url', '') for log in logs)
        return len(unique_urls)
    
    def calculate_method_entropy(self, ip_address: str = None, window_key: str = '1min') -> float:
        """
        Menghitung entropi distribusi HTTP method.
        Entropi rendah = pola normal (mayoritas GET/POST)
        Entropi tinggi = pola abnormal (berbagai method)
        
        Args:
            ip_address: Filter by IP (None = semua IP)
            window_key: Window waktu
        
        Returns:
            Entropy value (0.0 - ~2.8 untuk 7 methods)
        """
        logs = self.get_logs_in_window(window_key)
        
        if ip_address:
            logs = [log for log in logs if log.get('ip_address') == ip_address]
        
        if not logs:
            return 0.0
        
        # Hitung distribusi method
        methods = [log.get('method', 'GET') for log in logs]
        total = len(methods)
        
        # Hitung probability tiap method
        method_counts = {}
        for method in methods:
            method_counts[method] = method_counts.get(method, 0) + 1
        
        # Hitung entropy: -sum(p * log2(p))
        entropy = 0.0
        for count in method_counts.values():
            p = count / total
            if p > 0:
                entropy -= p * np.log2(p)
        
        return entropy
    
    def extract_temporal_features(self, log_data: Dict) -> Dict:
        """
        Mengekstrak semua fitur temporal untuk satu log entry.
        
        Args:
            log_data: Dictionary berisi data log server
        
        Returns:
            Dictionary dengan semua fitur temporal
        """
        ip_address = log_data.get('ip_address', '')
        
        # Tambahkan log ke buffer dulu
        self.add_log(log_data)
        
        # Ekstrak semua fitur temporal
        features = {
            # Request frequency features
            'req_count_1min': self.calculate_request_count(ip_address, '1min'),
            'req_count_5min': self.calculate_request_count(ip_address, '5min'),
            
            # Response time features
            'avg_response_time_1min': round(self.calculate_avg_response_time(ip_address, '1min'), 2),
            'avg_response_time_5min': round(self.calculate_avg_response_time(ip_address, '5min'), 2),
            
            # Bandwidth features
            'avg_bytes_5min': round(self.calculate_avg_bytes(ip_address, '5min'), 2),
            
            # Error rate features
            'error_rate_1min': round(self.calculate_error_rate(ip_address, '1min'), 4),
            'error_rate_5min': round(self.calculate_error_rate(ip_address, '5min'), 4),
            'error_rate_slope': round(self.calculate_error_rate_slope(ip_address), 4),
            
            # Behavior features
            'unique_urls_1min': self.calculate_unique_urls(ip_address, '1min'),
            'method_entropy': round(self.calculate_method_entropy(ip_address, '1min'), 4),
            
            # Global metrics (semua IP)
            'global_req_count_1min': len(self.get_logs_in_window('1min')),
            'global_error_rate_1min': round(self.calculate_error_rate(None, '1min'), 4),
        }
        
        return features
    
    def get_feature_vector(self, log_data: Dict) -> np.ndarray:
        """
        Mengkonversi fitur temporal menjadi numpy array untuk ML model.
        
        Args:
            log_data: Dictionary berisi data log server
        
        Returns:
            Numpy array dengan fitur temporal
        """
        features = self.extract_temporal_features(log_data)
        
        # Urutkan fitur untuk konsistensi
        feature_order = [
            'req_count_1min',
            'req_count_5min',
            'avg_response_time_1min',
            'avg_bytes_5min',
            'error_rate_1min',
            'error_rate_slope',
            'unique_urls_1min',
            'method_entropy',
            'global_req_count_1min',
            'global_error_rate_1min'
        ]
        
        return np.array([features[key] for key in feature_order])
    
    def get_stats(self) -> Dict:
        """
        Mendapatkan statistik dari sliding window saat ini.
        
        Returns:
            Dictionary dengan statistik
        """
        with self.lock:
            return {
                'buffer_size': len(self.log_buffer),
                'window_size_minutes': self.window_size.total_seconds() / 60,
                'logs_1min': len(self.get_logs_in_window('1min')),
                'logs_5min': len(self.get_logs_in_window('5min')),
                'logs_10min': len(self.get_logs_in_window('10min')),
            }
    
    def clear(self) -> None:
        """
        Membersihkan buffer sliding window.
        """
        with self.lock:
            self.log_buffer.clear()
            print("[INFO] Sliding window buffer cleared")


# Global instance untuk digunakan di Flask app
sliding_window = TemporalSlidingWindow(window_size_minutes=10)


def get_sliding_window() -> TemporalSlidingWindow:
    """
    Mendapatkan global instance dari sliding window.
    
    Returns:
        TemporalSlidingWindow instance
    """
    return sliding_window


# ========================================
# UNIT TESTING (untuk PyTest)
# ========================================

if __name__ == '__main__':
    # Quick test
    sw = TemporalSlidingWindow(window_size_minutes=5)
    
    # Simulasi beberapa log
    test_logs = [
        {'ip_address': '192.168.1.100', 'method': 'GET', 'url': '/api/users', 'status_code': 200, 'response_time': 100},
        {'ip_address': '192.168.1.100', 'method': 'GET', 'url': '/api/posts', 'status_code': 200, 'response_time': 150},
        {'ip_address': '192.168.1.100', 'method': 'POST', 'url': '/api/login', 'status_code': 401, 'response_time': 50},
        {'ip_address': '10.0.0.1', 'method': 'GET', 'url': '/admin', 'status_code': 403, 'response_time': 30},
    ]
    
    for log in test_logs:
        features = sw.extract_temporal_features(log)
        print(f"\nIP: {log['ip_address']}")
        print(f"Features: {features}")
    
    print(f"\nStats: {sw.get_stats()}")
