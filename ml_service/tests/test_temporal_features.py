"""
========================================
UNIT TESTS - TEMPORAL SLIDING WINDOW
PyTest untuk validasi modul Temporal Features
========================================

Lead Researcher & Developer:
  MUHAMMAD AKBAR HADI PRATAMA (@el-pablos)
"""

import pytest
import numpy as np
from datetime import datetime, timedelta
import sys
import os

# Add parent directory to path
sys.path.insert(0, os.path.dirname(os.path.dirname(os.path.abspath(__file__))))

from temporal_features import TemporalSlidingWindow


class TestTemporalSlidingWindow:
    """Test suite untuk TemporalSlidingWindow class."""
    
    @pytest.fixture
    def sliding_window(self):
        """Fixture untuk membuat instance baru setiap test."""
        return TemporalSlidingWindow(window_size_minutes=5)
    
    def test_initialization(self, sliding_window):
        """Test inisialisasi sliding window."""
        assert sliding_window is not None
        assert sliding_window.window_size == timedelta(minutes=5)
        assert len(sliding_window.log_buffer) == 0
    
    def test_add_single_log(self, sliding_window):
        """Test menambahkan satu log ke buffer."""
        log = {
            'ip_address': '192.168.1.100',
            'method': 'GET',
            'url': '/api/users',
            'status_code': 200,
            'response_time': 100
        }
        sliding_window.add_log(log)
        
        assert len(sliding_window.log_buffer) == 1
        assert sliding_window.log_buffer[0]['ip_address'] == '192.168.1.100'
    
    def test_add_multiple_logs(self, sliding_window):
        """Test menambahkan multiple log."""
        for i in range(5):
            log = {
                'ip_address': f'192.168.1.{100 + i}',
                'method': 'GET',
                'url': f'/api/resource/{i}',
                'status_code': 200,
                'response_time': 100 + i * 10
            }
            sliding_window.add_log(log)
        
        assert len(sliding_window.log_buffer) == 5
    
    def test_request_count_same_ip(self, sliding_window):
        """Test menghitung request dari IP yang sama."""
        ip = '192.168.1.100'
        
        for i in range(10):
            sliding_window.add_log({
                'ip_address': ip,
                'method': 'GET',
                'url': f'/api/test/{i}',
                'status_code': 200,
                'response_time': 100
            })
        
        count = sliding_window.calculate_request_count(ip, '1min')
        assert count == 10
    
    def test_request_count_different_ips(self, sliding_window):
        """Test request count untuk IP berbeda."""
        # 5 request dari IP1
        for i in range(5):
            sliding_window.add_log({
                'ip_address': '192.168.1.100',
                'method': 'GET',
                'url': '/test',
                'status_code': 200,
                'response_time': 100
            })
        
        # 3 request dari IP2
        for i in range(3):
            sliding_window.add_log({
                'ip_address': '192.168.1.200',
                'method': 'POST',
                'url': '/test',
                'status_code': 200,
                'response_time': 100
            })
        
        assert sliding_window.calculate_request_count('192.168.1.100', '1min') == 5
        assert sliding_window.calculate_request_count('192.168.1.200', '1min') == 3
    
    def test_average_response_time(self, sliding_window):
        """Test kalkulasi rata-rata response time."""
        ip = '192.168.1.100'
        response_times = [100, 200, 300, 400, 500]
        
        for rt in response_times:
            sliding_window.add_log({
                'ip_address': ip,
                'method': 'GET',
                'url': '/test',
                'status_code': 200,
                'response_time': rt
            })
        
        avg = sliding_window.calculate_avg_response_time(ip, '1min')
        expected = np.mean(response_times)
        
        assert abs(avg - expected) < 0.01
    
    def test_error_rate_calculation(self, sliding_window):
        """Test kalkulasi error rate."""
        ip = '192.168.1.100'
        
        # 6 success, 4 error = 40% error rate
        for status in [200, 200, 200, 200, 200, 200, 400, 404, 500, 503]:
            sliding_window.add_log({
                'ip_address': ip,
                'method': 'GET',
                'url': '/test',
                'status_code': status,
                'response_time': 100
            })
        
        error_rate = sliding_window.calculate_error_rate(ip, '1min')
        assert abs(error_rate - 0.4) < 0.01
    
    def test_unique_urls_count(self, sliding_window):
        """Test menghitung URL unik (deteksi scanning)."""
        ip = '192.168.1.100'
        urls = ['/api/users', '/api/posts', '/api/users', '/admin', '/login']
        
        for url in urls:
            sliding_window.add_log({
                'ip_address': ip,
                'method': 'GET',
                'url': url,
                'status_code': 200,
                'response_time': 100
            })
        
        unique_count = sliding_window.calculate_unique_urls(ip, '1min')
        assert unique_count == 4  # /api/users duplikat
    
    def test_method_entropy(self, sliding_window):
        """Test kalkulasi entropy HTTP method."""
        ip = '192.168.1.100'
        
        # Semua GET = entropy rendah
        for _ in range(10):
            sliding_window.add_log({
                'ip_address': ip,
                'method': 'GET',
                'url': '/test',
                'status_code': 200,
                'response_time': 100
            })
        
        entropy = sliding_window.calculate_method_entropy(ip, '1min')
        assert entropy == 0.0  # Semua sama = 0 entropy
    
    def test_method_entropy_mixed(self, sliding_window):
        """Test entropy dengan mixed methods."""
        ip = '192.168.1.100'
        methods = ['GET', 'POST', 'PUT', 'DELETE']
        
        for method in methods:
            sliding_window.add_log({
                'ip_address': ip,
                'method': method,
                'url': '/test',
                'status_code': 200,
                'response_time': 100
            })
        
        entropy = sliding_window.calculate_method_entropy(ip, '1min')
        assert entropy > 0  # Ada variasi = entropy > 0
        assert entropy == 2.0  # 4 items dengan probabilitas sama = log2(4) = 2
    
    def test_extract_temporal_features(self, sliding_window):
        """Test ekstraksi semua fitur temporal."""
        log = {
            'ip_address': '192.168.1.100',
            'method': 'GET',
            'url': '/api/users',
            'status_code': 200,
            'response_time': 150
        }
        
        features = sliding_window.extract_temporal_features(log)
        
        # Pastikan semua fitur ada
        expected_keys = [
            'req_count_1min', 'req_count_5min',
            'avg_response_time_1min', 'avg_response_time_5min',
            'avg_bytes_5min', 'error_rate_1min', 'error_rate_5min',
            'error_rate_slope', 'unique_urls_1min', 'method_entropy',
            'global_req_count_1min', 'global_error_rate_1min'
        ]
        
        for key in expected_keys:
            assert key in features, f"Missing key: {key}"
    
    def test_get_feature_vector(self, sliding_window):
        """Test konversi fitur ke numpy array."""
        log = {
            'ip_address': '192.168.1.100',
            'method': 'GET',
            'url': '/api/users',
            'status_code': 200,
            'response_time': 150
        }
        
        vector = sliding_window.get_feature_vector(log)
        
        assert isinstance(vector, np.ndarray)
        assert len(vector) == 10  # 10 temporal features
    
    def test_get_stats(self, sliding_window):
        """Test mendapatkan statistik sliding window."""
        # Tambah beberapa log
        for i in range(5):
            sliding_window.add_log({
                'ip_address': '192.168.1.100',
                'method': 'GET',
                'url': '/test',
                'status_code': 200,
                'response_time': 100
            })
        
        stats = sliding_window.get_stats()
        
        assert 'buffer_size' in stats
        assert 'window_size_minutes' in stats
        assert stats['buffer_size'] == 5
    
    def test_clear_buffer(self, sliding_window):
        """Test membersihkan buffer."""
        # Isi buffer
        for i in range(10):
            sliding_window.add_log({
                'ip_address': '192.168.1.100',
                'method': 'GET',
                'url': '/test',
                'status_code': 200,
                'response_time': 100
            })
        
        assert len(sliding_window.log_buffer) == 10
        
        sliding_window.clear()
        
        assert len(sliding_window.log_buffer) == 0


class TestDDoSDetection:
    """Test case untuk skenario DDoS detection."""
    
    @pytest.fixture
    def sliding_window(self):
        return TemporalSlidingWindow(window_size_minutes=5)
    
    def test_high_request_rate_detection(self, sliding_window):
        """Simulasi DDoS dengan request rate tinggi."""
        attacker_ip = '10.0.0.1'
        
        # Simulasi 1000 request dalam waktu singkat
        for i in range(1000):
            sliding_window.add_log({
                'ip_address': attacker_ip,
                'method': 'GET',
                'url': '/api/target',
                'status_code': 200,
                'response_time': 50  # Response time rendah (server belum overload)
            })
        
        req_count = sliding_window.calculate_request_count(attacker_ip, '1min')
        
        # Assert: Request count sangat tinggi = indikasi DDoS
        assert req_count >= 1000
    
    def test_normal_user_vs_attacker(self, sliding_window):
        """Bandingkan pola normal user vs attacker."""
        # Normal user: 5 request
        for i in range(5):
            sliding_window.add_log({
                'ip_address': '192.168.1.10',
                'method': 'GET',
                'url': f'/page/{i}',
                'status_code': 200,
                'response_time': 200
            })
        
        # Attacker: 500 request
        for i in range(500):
            sliding_window.add_log({
                'ip_address': '10.0.0.1',
                'method': 'GET',
                'url': '/api/login',
                'status_code': 401,
                'response_time': 30
            })
        
        normal_count = sliding_window.calculate_request_count('192.168.1.10', '1min')
        attacker_count = sliding_window.calculate_request_count('10.0.0.1', '1min')
        attacker_error_rate = sliding_window.calculate_error_rate('10.0.0.1', '1min')
        
        assert normal_count == 5
        assert attacker_count == 500
        assert attacker_error_rate == 1.0  # 100% error (401)


if __name__ == '__main__':
    pytest.main([__file__, '-v'])
