#!/usr/bin/env python3
"""
╔══════════════════════════════════════════════════════════════════════════════╗
║                   LOG SENTINEL - TRAFFIC SIMULATOR                            ║
║            "Noise Maker" untuk Testing Real-time Dashboard                    ║
╚══════════════════════════════════════════════════════════════════════════════╝

Script ini membanjiri sistem dengan traffic dummy untuk menguji:
- Temporal Sliding Window (angka harus bergerak)
- Ensemble Voting (grafik harus terupdate)
- Error Rate, Method Entropy, Burst Score

Distribusi Traffic:
  - 80% Request Normal (200 OK, berbagai URL)
  - 15% Request Suspicious (404 Not Found beruntun, scanning)
  - 5%  Request Serangan (Payload besar, burst DDoS, SQL Injection)

================================================================================
Usage: python simulate_traffic.py [--speed FAST|NORMAL|SLOW] [--duration MINUTES]
================================================================================
"""

import requests
import time
import random
import argparse
from datetime import datetime
from typing import Dict, List, Tuple
import threading
import sys

# Konfigurasi ML Service
ML_SERVICE_URL = "http://127.0.0.1:5000"

# ========================================
# DATA POOLS UNTUK SIMULASI
# ========================================

# Pool IP Address (simulasi berbagai sumber)
NORMAL_IPS = [
    "192.168.1.100", "192.168.1.101", "192.168.1.102",
    "10.0.0.50", "10.0.0.51", "10.0.0.52",
    "172.16.0.10", "172.16.0.11"
]

SUSPICIOUS_IPS = [
    "45.33.32.156",  # Known scanner
    "185.220.101.42",  # Tor exit node
    "91.240.118.x"  # Generic suspicious
]

ATTACK_IPS = [
    "123.45.67.89",  # Botnet IP
    "98.76.54.32",   # DDoS source
    "111.222.333.44"  # Attacker
]

# Pool URL (simulasi berbagai akses)
NORMAL_URLS = [
    "/api/users", "/api/products", "/api/orders",
    "/dashboard", "/profile", "/settings",
    "/static/css/main.css", "/static/js/app.js",
    "/images/logo.png", "/favicon.ico"
]

SUSPICIOUS_URLS = [
    "/admin", "/wp-admin", "/phpmyadmin",
    "/.env", "/.git/config", "/config.php",
    "/backup.sql", "/db.sql", "/dump.sql",
    "/actuator/health", "/actuator/env"
]

ATTACK_URLS = [
    "/api/users?id=1' OR '1'='1",  # SQL Injection
    "/api/login?user=admin&pass=' OR '1'='1",
    "/../../etc/passwd",  # Path Traversal
    "/api/exec?cmd=cat%20/etc/passwd",  # Command Injection
    "/api/users?id=<script>alert('xss')</script>"  # XSS
]

# User Agents
NORMAL_USER_AGENTS = [
    "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/120.0.0.0",
    "Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) AppleWebKit/605.1.15",
    "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 Safari/537.36"
]

SUSPICIOUS_USER_AGENTS = [
    "sqlmap/1.4.11",
    "nikto/2.1.6",
    "gobuster/3.1.0",
    "python-requests/2.28.0",
    "curl/7.64.1"
]

ATTACK_USER_AGENTS = [
    "() { :; }; /bin/bash -c 'cat /etc/passwd'",  # Shellshock
    "masscan/1.0",
    "DirBuster-1.0-RC1"
]

# HTTP Methods
NORMAL_METHODS = ["GET", "GET", "GET", "GET", "POST", "PUT"]  # GET heavy
SUSPICIOUS_METHODS = ["GET", "HEAD", "OPTIONS", "TRACE"]
ATTACK_METHODS = ["POST", "PUT", "DELETE", "PATCH"]


class TrafficSimulator:
    """Simulator untuk membangkitkan traffic ke ML Service."""
    
    def __init__(self, speed: str = "NORMAL"):
        self.running = False
        self.stats = {
            'normal': 0,
            'suspicious': 0,
            'attack': 0,
            'errors': 0,
            'total': 0
        }
        
        # Konfigurasi kecepatan
        speed_config = {
            'SLOW': {'normal': 2.0, 'suspicious': 1.5, 'attack': 0.5},
            'NORMAL': {'normal': 0.5, 'suspicious': 0.3, 'attack': 0.1},
            'FAST': {'normal': 0.1, 'suspicious': 0.05, 'attack': 0.02},
            'TURBO': {'normal': 0.02, 'suspicious': 0.01, 'attack': 0.005}
        }
        self.delays = speed_config.get(speed.upper(), speed_config['NORMAL'])
        
        print(f"\n[SIMULATOR] Speed mode: {speed.upper()}")
        print(f"[SIMULATOR] Delays - Normal: {self.delays['normal']}s, Suspicious: {self.delays['suspicious']}s, Attack: {self.delays['attack']}s")
    
    def generate_normal_request(self) -> Dict:
        """Generate request normal."""
        return {
            'ip_address': random.choice(NORMAL_IPS),
            'method': random.choice(NORMAL_METHODS),
            'url': random.choice(NORMAL_URLS),
            'status_code': random.choices([200, 201, 204, 301, 302], weights=[70, 10, 5, 10, 5])[0],
            'user_agent': random.choice(NORMAL_USER_AGENTS),
            'response_time': random.uniform(50, 200),  # 50-200ms normal
            'bytes_sent': random.randint(500, 5000)
        }
    
    def generate_suspicious_request(self) -> Dict:
        """Generate request suspicious (scanning, probing)."""
        return {
            'ip_address': random.choice(SUSPICIOUS_IPS),
            'method': random.choice(SUSPICIOUS_METHODS),
            'url': random.choice(SUSPICIOUS_URLS),
            'status_code': random.choices([404, 403, 401, 500], weights=[50, 30, 15, 5])[0],
            'user_agent': random.choice(SUSPICIOUS_USER_AGENTS),
            'response_time': random.uniform(10, 50),  # Scanner biasanya cepat
            'bytes_sent': random.randint(100, 500)
        }
    
    def generate_attack_request(self) -> Dict:
        """Generate request attack (injection, DDoS, etc)."""
        return {
            'ip_address': random.choice(ATTACK_IPS),
            'method': random.choice(ATTACK_METHODS),
            'url': random.choice(ATTACK_URLS),
            'status_code': random.choices([200, 500, 403], weights=[20, 50, 30])[0],
            'user_agent': random.choice(ATTACK_USER_AGENTS),
            'response_time': random.uniform(5, 30),  # Attack cepat
            'bytes_sent': random.randint(10000, 100000)  # Payload besar
        }
    
    def send_request(self, request_data: Dict, request_type: str) -> bool:
        """Kirim request ke ML Service."""
        try:
            response = requests.post(
                f"{ML_SERVICE_URL}/predict/ensemble",
                json=request_data,
                timeout=5
            )
            
            if response.status_code == 200:
                result = response.json()
                threat = result.get('data', {}).get('threat_level', 'unknown')
                return True, threat
            else:
                return False, None
                
        except requests.exceptions.RequestException as e:
            return False, str(e)
    
    def send_burst_attack(self, count: int = 20):
        """
        Simulasi DDoS burst - banyak request dalam waktu singkat.
        """
        print(f"\n{'='*60}")
        print(f"🔥 [ATTACK] INJECTING DDoS BURST: {count} requests in 1 second!")
        print(f"{'='*60}")
        
        attack_ip = random.choice(ATTACK_IPS)
        
        for i in range(count):
            request_data = {
                'ip_address': attack_ip,
                'method': 'GET',
                'url': f'/api/flood/{i}',
                'status_code': 200,
                'user_agent': 'LOIC/1.0',
                'response_time': random.uniform(1, 10),
                'bytes_sent': random.randint(100, 500)
            }
            
            try:
                requests.post(
                    f"{ML_SERVICE_URL}/predict/ensemble",
                    json=request_data,
                    timeout=1
                )
                self.stats['attack'] += 1
                self.stats['total'] += 1
            except:
                self.stats['errors'] += 1
        
        print(f"✓ DDoS burst complete - {count} requests sent from {attack_ip}")
    
    def print_stats(self):
        """Print statistik saat ini."""
        print(f"\n[STATS] Total: {self.stats['total']} | "
              f"Normal: {self.stats['normal']} | "
              f"Suspicious: {self.stats['suspicious']} | "
              f"Attack: {self.stats['attack']} | "
              f"Errors: {self.stats['errors']}")
    
    def run(self, duration_minutes: int = None):
        """
        Jalankan simulator.
        
        Args:
            duration_minutes: Durasi dalam menit (None = infinite)
        """
        self.running = True
        start_time = time.time()
        batch_counter = 0
        
        print("\n" + "="*60)
        print("🚀 LOG SENTINEL - TRAFFIC SIMULATOR STARTED")
        print("="*60)
        print(f"📊 Target: {ML_SERVICE_URL}")
        print(f"⏱️  Duration: {'∞ (Ctrl+C to stop)' if duration_minutes is None else f'{duration_minutes} minutes'}")
        print(f"📈 Distribution: 80% Normal | 15% Suspicious | 5% Attack")
        print("="*60 + "\n")
        
        try:
            while self.running:
                # Check durasi
                if duration_minutes:
                    elapsed = (time.time() - start_time) / 60
                    if elapsed >= duration_minutes:
                        print(f"\n⏰ Duration reached ({duration_minutes} minutes). Stopping...")
                        break
                
                # Random traffic type berdasarkan distribusi
                rand = random.random()
                
                if rand < 0.80:  # 80% Normal
                    request_data = self.generate_normal_request()
                    request_type = "NORMAL"
                    delay = self.delays['normal']
                    icon = "✅"
                    
                elif rand < 0.95:  # 15% Suspicious
                    request_data = self.generate_suspicious_request()
                    request_type = "SUSPICIOUS"
                    delay = self.delays['suspicious']
                    icon = "⚠️"
                    
                else:  # 5% Attack
                    # Kadang burst, kadang single
                    if random.random() < 0.3:  # 30% chance burst
                        self.send_burst_attack(random.randint(10, 30))
                        batch_counter += 1
                        continue
                    else:
                        request_data = self.generate_attack_request()
                        request_type = "ATTACK"
                        delay = self.delays['attack']
                        icon = "🔴"
                
                # Kirim request
                success, threat = self.send_request(request_data, request_type)
                
                if success:
                    self.stats[request_type.lower()] += 1
                    self.stats['total'] += 1
                    
                    # Print progress setiap 10 request
                    if self.stats['total'] % 10 == 0:
                        print(f"{icon} [{request_type}] Sent {self.stats['total']} requests | "
                              f"Last: {request_data['ip_address']} → {request_data['url'][:30]} | "
                              f"Detected: {threat}")
                else:
                    self.stats['errors'] += 1
                
                # Print stats setiap 50 request
                if self.stats['total'] % 50 == 0:
                    self.print_stats()
                
                # Delay
                time.sleep(delay)
                
                # Random jeda untuk realistis
                if random.random() < 0.1:  # 10% chance extra delay
                    time.sleep(random.uniform(0.5, 2.0))
                    
        except KeyboardInterrupt:
            print("\n\n⛔ Simulator stopped by user (Ctrl+C)")
        
        finally:
            self.running = False
            print("\n" + "="*60)
            print("📊 FINAL STATISTICS")
            print("="*60)
            self.print_stats()
            elapsed = time.time() - start_time
            print(f"⏱️  Total runtime: {elapsed:.1f} seconds ({elapsed/60:.1f} minutes)")
            print(f"📈 Requests per second: {self.stats['total']/elapsed:.2f}")
            print("="*60)


def check_ml_service():
    """Cek apakah ML Service berjalan."""
    try:
        response = requests.get(f"{ML_SERVICE_URL}/health", timeout=5)
        if response.status_code == 200:
            print("✅ ML Service is running!")
            return True
        else:
            print(f"⚠️ ML Service returned status {response.status_code}")
            return False
    except requests.exceptions.ConnectionError:
        print("❌ ERROR: ML Service tidak berjalan!")
        print(f"   Pastikan Flask berjalan di {ML_SERVICE_URL}")
        print("   Jalankan: python app.py")
        return False
    except Exception as e:
        print(f"❌ ERROR: {e}")
        return False


def main():
    """Main entry point."""
    parser = argparse.ArgumentParser(
        description="Log Sentinel Traffic Simulator",
        formatter_class=argparse.RawDescriptionHelpFormatter,
        epilog="""
Examples:
  python simulate_traffic.py                    # Normal speed, infinite
  python simulate_traffic.py --speed FAST       # Fast mode
  python simulate_traffic.py --speed TURBO      # Maximum speed
  python simulate_traffic.py --duration 5       # Run for 5 minutes
  python simulate_traffic.py --speed FAST -d 2  # Fast mode, 2 minutes
        """
    )
    
    parser.add_argument(
        '--speed', '-s',
        choices=['SLOW', 'NORMAL', 'FAST', 'TURBO'],
        default='NORMAL',
        help='Speed mode: SLOW, NORMAL, FAST, TURBO (default: NORMAL)'
    )
    
    parser.add_argument(
        '--duration', '-d',
        type=int,
        default=None,
        help='Duration in minutes (default: infinite until Ctrl+C)'
    )
    
    parser.add_argument(
        '--url', '-u',
        default='http://127.0.0.1:5000',
        help='ML Service URL (default: http://127.0.0.1:5000)'
    )
    
    args = parser.parse_args()
    
    # Update global URL
    global ML_SERVICE_URL
    ML_SERVICE_URL = args.url
    
    print("\n" + "="*60)
    print("        LOG SENTINEL - TRAFFIC SIMULATOR v1.0")
    print("="*60)
    
    # Check ML Service
    if not check_ml_service():
        sys.exit(1)
    
    # Run simulator
    simulator = TrafficSimulator(speed=args.speed)
    simulator.run(duration_minutes=args.duration)


if __name__ == '__main__':
    main()
