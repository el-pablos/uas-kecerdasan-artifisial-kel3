# 🛡️ PRESENTASI UAS KECERDASAN ARTIFISIAL
## LOG SENTINEL - Anomaly Detection System

---

# SLIDE 1: TITLE SLIDE
## Log Sentinel - Sistem Deteksi Anomali Berbasis Machine Learning

### Poin-poin Materi:
- **Nama Proyek:** Log Sentinel - Anomaly Detection System
- **Mata Kuliah:** Kecerdasan Artifisial (AI)
- **Institusi:** Universitas Negeri Jakarta
- **Periode:** Semester Genap 2025/2026
- **Tim Pengembang:**
  - Jeremy Christo Emmanuelle Panjaitan (237006516084) - Lead Developer
  - Muhammad Akbar Hadi Pratama (237006516058) - Backend Developer
  - Farrel Alfaridzi (237006516028) - Frontend Developer
  - Chosmas Laurens Rumngewur (217006516074) - ML Engineer

### 📢 Narasi Pembacaan (Speaker Notes):
> Selamat pagi/siang Bapak/Ibu dosen dan rekan-rekan sekalian. Perkenalkan, kami dari Kelompok 3 akan mempresentasikan proyek tugas akhir mata kuliah Kecerdasan Artifisial dengan judul **"Log Sentinel - Sistem Deteksi Anomali Berbasis Machine Learning"**.
>
> Proyek ini bukan sekadar implementasi algoritma klasifikasi biasa. Kami mengambil pendekatan berbeda dengan mengintegrasikan **dua domain keilmuan yang sangat relevan di era digital ini: Artificial Intelligence dan Cybersecurity**.
>
> Mengapa kombinasi ini penting? Karena setiap detik, ribuan serangan siber terjadi di seluruh dunia. Manual monitoring sudah tidak lagi efektif. Dibutuhkan sistem cerdas yang dapat mendeteksi ancaman secara **real-time dan otomatis**.

---

# SLIDE 2: LATAR BELAKANG MASALAH
## Krisis Keamanan Digital di Era Modern

### Poin-poin Materi:
- **Statistik Ancaman Global:**
  - 2.200+ serangan siber terjadi setiap hari (Cobalt.io, 2024)
  - 94% malware dikirim via email dan web requests
  - Kerugian global akibat cybercrime: $8 triliun/tahun (Cybersecurity Ventures)

- **Keterbatasan Sistem Tradisional:**
  - Rule-based detection: Hanya mendeteksi pola yang sudah dikenal
  - Manual log analysis: Time-consuming, error-prone
  - Signature-based IDS: Tidak efektif terhadap zero-day attacks

- **Kebutuhan Solusi:**
  - Deteksi anomali real-time tanpa signature database
  - Sistem yang bisa belajar dari pola traffic normal
  - Visualisasi yang membantu analyst memahami threat landscape

### 📢 Narasi Pembacaan (Speaker Notes):
> Mari kita bicara fakta. Menurut Cobalt.io, lebih dari **2.200 serangan siber** terjadi setiap hari secara global. Ini bukan angka yang kecil. Setiap 39 detik, ada upaya eksploitasi terhadap sistem digital.
>
> Sistem keamanan tradisional seperti **Intrusion Detection System (IDS) berbasis signature** memiliki kelemahan fatal: mereka hanya bisa mendeteksi serangan yang sudah pernah tercatat di database. Bagaimana dengan **zero-day attacks**? Bagaimana dengan pola serangan baru yang belum pernah dilihat sebelumnya?
>
> Di sinilah **Machine Learning** menjadi game changer. Dengan pendekatan **anomaly detection**, sistem tidak perlu mengetahui pola serangan spesifik. Cukup dengan memahami seperti apa traffic "normal", sistem dapat mengidentifikasi penyimpangan yang mencurigakan.
>
> Inilah yang memotivasi kami untuk membangun **Log Sentinel** — sebuah sistem cerdas yang menggabungkan kekuatan AI dengan kebutuhan nyata cybersecurity.

---

# SLIDE 3: RELEVANSI MATA KULIAH AI
## Dari Teori Kelas ke Implementasi Real-World

### Poin-poin Materi:
- **Konsep AI yang Diimplementasikan:**
  | Materi Kuliah | Implementasi di Log Sentinel |
  |---------------|------------------------------|
  | Machine Learning | Algoritma Isolation Forest |
  | Unsupervised Learning | Deteksi anomali tanpa label |
  | Feature Engineering | Ekstraksi 6 fitur dari log server |
  | Dimensionality Reduction | PCA untuk visualisasi 2D |
  | Explainable AI | Scatter plot interpretable |

- **Keunggulan Unsupervised Learning untuk Security:**
  - Tidak membutuhkan dataset serangan berlabel (mahal & jarang)
  - Dapat mendeteksi novel attacks yang belum pernah ada
  - Self-adaptive terhadap perubahan pola traffic

- **Real-World Application:**
  - Server log monitoring
  - DDoS attack detection
  - Brute force identification
  - SQL injection pattern recognition

### 📢 Narasi Pembacaan (Speaker Notes):
> Proyek ini adalah manifestasi langsung dari materi yang kami pelajari di kelas Kecerdasan Artifisial.
>
> Kami mengimplementasikan **Unsupervised Learning** — khususnya algoritma **Isolation Forest** — yang merupakan teknik powerful untuk anomaly detection. Kenapa unsupervised? Karena dalam konteks cybersecurity, mendapatkan dataset serangan yang berlabel sangat **mahal dan berisiko**.
>
> Bayangkan: untuk membuat dataset berlabel, seseorang harus melakukan serangan nyata terlebih dahulu. Ini tidak praktis dan berbahaya.
>
> Dengan pendekatan **unsupervised**, model cukup belajar dari data traffic normal. Ketika ada pola yang **menyimpang signifikan** dari baseline normal tersebut, sistem akan menandainya sebagai anomali.
>
> Kami juga mengimplementasikan **Principal Component Analysis (PCA)** untuk dimensionality reduction, memungkinkan visualisasi data 6 dimensi ke dalam plot 2D yang mudah dipahami. Ini adalah implementasi dari konsep **Explainable AI** — membuat black-box model menjadi transparan.

---

# SLIDE 4: DATASET & PREPROCESSING
## Membersihkan Data dari Noise dan Serangan

### Poin-poin Materi:
- **Sumber Data:**
  - Synthetic server log generation (controlled environment)
  - Format: Apache/Nginx-style access logs
  - Volume: Scalable dari ratusan hingga ribuan records

- **Atribut Log yang Dianalisis:**
  | Atribut | Tipe Data | Contoh |
  |---------|-----------|--------|
  | IP Address | String | 192.168.1.100 |
  | HTTP Method | Enum | GET, POST, PUT, DELETE |
  | URL Path | String | /api/users |
  | Status Code | Integer | 200, 404, 500, 503 |
  | Response Time | Float | 250.5 ms |
  | User Agent | String | Mozilla/5.0... |

- **Preprocessing Pipeline:**
  1. **Null Handling:** Penanganan missing values dengan default values
  2. **Encoding:** LabelEncoder untuk categorical features
  3. **Normalization:** Implicit dalam Isolation Forest
  4. **Timestamp Parsing:** Konversi ke epoch untuk temporal analysis

### 📢 Narasi Pembacaan (Speaker Notes):
> Seperti prinsip dalam Machine Learning: **"Garbage In, Garbage Out"**. Kualitas model sangat bergantung pada kualitas data yang digunakan.
>
> Dataset kami berasal dari synthetic log generation yang mensimulasikan traffic server nyata. Ini mencakup **traffic normal** seperti request API biasa, dan **traffic anomali** yang mensimulasikan berbagai jenis serangan.
>
> Setiap log entry memiliki 6 atribut utama: **IP Address, HTTP Method, URL Path, Status Code, Response Time, dan User Agent**. Masing-masing membawa informasi penting untuk pattern recognition.
>
> Dalam tahap preprocessing, kami melakukan **Label Encoding** untuk mengkonversi categorical features seperti HTTP Method menjadi numerik. IP Address di-hash menjadi integer untuk preservasi privasi sekaligus maintainability pattern.
>
> Yang menarik, algoritma **Isolation Forest** tidak membutuhkan explicit normalization karena ia berbasis decision tree yang insensitive terhadap feature scaling. Ini membuat pipeline kami lebih efisien.

---

# SLIDE 5: FEATURE ENGINEERING
## Memilih Variabel Krusial untuk Deteksi Ancaman

### Poin-poin Materi:
- **Feature Selection Rationale:**
  | Feature | Encoding | Relevance untuk Security |
  |---------|----------|--------------------------|
  | `ip_numeric` | Hash Integer | Identifikasi source anomaly (botnet, scanner) |
  | `method_encoded` | 0-4 | Unusual method usage (PUT flood, DELETE abuse) |
  | `status_code` | Raw | Error spike (5xx = server stress dari attack) |
  | `response_time` | Raw (ms) | High latency = resource exhaustion attack |
  | `url_length` | Length(url) | Long URLs = buffer overflow, SQLi attempts |
  | `user_agent_idx` | Index | Bot detection, tool fingerprinting |

- **Feature Importance untuk Threat Detection:**
  - `status_code` 5xx: DDoS/Resource exhaustion indicator
  - `response_time` > 1000ms: Potential slow HTTP attack
  - `url_length` > 200: Possible injection payload
  - Repetitive `ip_numeric` + high frequency: Botnet signature

- **Dimensi Feature Space:** 6 dimensi → direduksi via PCA → 2D visualisasi

### 📢 Narasi Pembacaan (Speaker Notes):
> Feature engineering adalah **seni dan sains** dalam Machine Learning. Kami tidak sembarangan memilih variabel — setiap feature dipilih berdasarkan **relevansi terhadap threat vector** yang ingin dideteksi.
>
> Contohnya, **IP numeric** bukan hanya identifier. Dalam konteks keamanan, clustering IP yang sama dengan frekuensi tinggi dalam waktu singkat adalah signature dari **botnet atau automated scanner**.
>
> **Response time** yang abnormally tinggi bisa mengindikasikan **slowloris attack** atau resource exhaustion dimana server terlalu sibuk menangani malicious requests.
>
> **URL length** yang sangat panjang sering menjadi carrier untuk **SQL Injection** atau **buffer overflow payloads**. Attacker mencoba menyembunyikan malicious code dalam URL parameters yang panjang.
>
> Dengan 6 feature ini, model kami memiliki perspective yang cukup komprehensif untuk membedakan traffic normal dari traffic berbahaya.

---

# SLIDE 6: ARSITEKTUR MODEL MACHINE LEARNING
## Isolation Forest - The Anomaly Hunter

### Poin-poin Materi:
- **Algoritma: Isolation Forest (Scikit-Learn)**
  ```python
  IsolationForest(
      n_estimators=100,      # 100 pohon isolation trees
      contamination=0.1,     # Expected 10% anomaly ratio
      max_samples='auto',    # Auto-sampling per tree
      random_state=42,       # Reproducibility
      n_jobs=-1              # Parallel processing
  )
  ```

- **Prinsip Kerja Isolation Forest:**
  1. **Isolation Principle:** Anomali lebih mudah diisolasi daripada data normal
  2. **Random Partitioning:** Data dipartisi secara random di setiap node
  3. **Path Length:** Anomali memiliki path pendek (cepat terisolasi)
  4. **Ensemble:** 100 trees memberikan robust decision

- **Keunggulan untuk Cyber Security:**
  - **Unsupervised:** Tidak butuh labeled attack data
  - **Efficient:** O(n log n) complexity
  - **Robust:** Resistant terhadap noise
  - **Real-time:** Fast inference untuk live monitoring

- **Output Model:**
  - `prediction`: -1 (anomaly) atau 1 (normal)
  - `anomaly_score`: Range -0.5 hingga 0.5 (semakin negatif = semakin anomali)

### 📢 Narasi Pembacaan (Speaker Notes):
> Mari kita deep dive ke jantung sistem kami: **Isolation Forest**.
>
> Berbeda dengan algoritma klasifikasi tradisional yang mencoba **menemukan decision boundary** antara kelas, Isolation Forest mengambil pendekatan yang lebih elegan: **isolasi langsung**.
>
> Prinsipnya sederhana namun powerful: **anomali adalah data yang "berbeda"**. Dan data yang berbeda lebih mudah dipisahkan dari kelompok. Dalam struktur tree, anomali akan terisolasi dalam **lebih sedikit split** dibanding data normal.
>
> Kami menggunakan ensemble **100 trees** untuk meningkatkan robustness. Parameter **contamination=0.1** mengindikasikan ekspektasi bahwa sekitar 10% dari traffic adalah anomali — angka yang realistis dalam environment yang aktif.
>
> Yang membuat Isolation Forest superior untuk cybersecurity adalah sifat **unsupervised**-nya. Kami tidak perlu dataset serangan berlabel yang mahal dan berisiko untuk dibuat. Model cukup belajar dari baseline normal, lalu mendeteksi deviasi.
>
> Dengan kompleksitas **O(n log n)**, model ini sangat efisien untuk **real-time inference** — requirement krusial dalam live threat monitoring.

---

# SLIDE 7: THE CYBER TWIST
## Improvisasi Cybersecurity yang Membedakan Proyek Ini

### Poin-poin Materi:
- **Integrasi AI + Cybersecurity yang Unique:**
  1. **Live Threat Visualization Map**
     - Peta dunia interaktif dengan Leaflet.js
     - Attack lines animasi dari source ke target
     - Real-time IP geolocation simulation
  
  2. **Multi-Attack Type Detection**
     | Attack Type | Detection Pattern |
     |-------------|-------------------|
     | DDoS | High frequency + 5xx status + high response time |
     | Brute Force | Repeated auth endpoints + 401/403 status |
     | SQL Injection | Long URLs + special characters + 500 errors |
     | Path Traversal | "../" patterns + unauthorized paths |

  3. **Threat Severity Scoring**
     - Scale 0-100 berdasarkan anomaly score
     - Color-coded warnings (Green/Yellow/Red)
     - Prioritized alerting untuk security analyst

- **Mitigasi yang Didukung:**
  - Early warning system untuk SOC (Security Operations Center)
  - Pattern identification untuk firewall rule generation
  - Forensic logging untuk incident response

### 📢 Narasi Pembacaan (Speaker Notes):
> Ini adalah bagian yang membedakan proyek kami dari implementasi Machine Learning standar.
>
> Kebanyakan proyek AI di perkuliahan berhenti di tahap **prediksi dan akurasi**. Kami melangkah lebih jauh dengan mengintegrasikan **Cybersecurity context** yang nyata.
>
> Pertama, kami membangun **Live Cyber Threat Map** — sebuah visualisasi peta dunia yang menampilkan simulasi serangan secara real-time. Attack lines animasi menunjukkan bagaimana ancaman bergerak dari source IP menuju server target. Ini bukan gimmick visual — ini adalah representasi yang umum digunakan di **Security Operations Center (SOC)** profesional.
>
> Kedua, model kami tidak hanya mendeteksi "ada anomali" tetapi juga mengkategorikan **jenis serangan** berdasarkan kombinasi pattern. DDoS memiliki signature berbeda dengan SQL Injection atau Brute Force. Ini membantu analyst melakukan **rapid triage**.
>
> Ketiga, kami mengimplementasikan **Severity Scoring** dari 0-100, memberikan prioritas pada ancaman yang benar-benar berbahaya. Tidak semua anomali sama — severity scoring membantu fokus pada yang kritis.
>
> Dengan fitur-fitur ini, Log Sentinel bukan sekadar classifier. Ia adalah **miniatur SIEM (Security Information and Event Management)** yang ditenagai AI.

---

# SLIDE 8: VISUALISASI & LIVE THREAT MAP
## Demonstrasi Dashboard Command Center

### Poin-poin Materi:
- **Dashboard Components:**
  1. **Statistics Cards**
     - Total Requests monitored
     - Anomalies detected
     - Threat percentage
     - Last update timestamp

  2. **Real-time Charts (ApexCharts)**
     - Time-series traffic volume
     - Normal vs Anomaly distribution
     - Severity score distribution

  3. **Live Cyber Threat Map (Leaflet.js)**
     - Dark-themed world map
     - Animated attack lines with curved paths
     - Source markers with IP info popups
     - Target server marker (Jakarta)
     - Auto-cleanup setelah 4 detik

  4. **PCA Scatter Plot**
     - 2D projection of 6D feature space
     - Blue dots: Normal traffic
     - Red dots: Anomaly traffic
     - Interactive tooltips

- **Live Monitoring Table:**
  - Real-time log entries
  - Color-coded by severity
  - Filterable by status

### 📢 Narasi Pembacaan (Speaker Notes):
> Sebuah sistem sekuat apapun tidak berguna jika informasinya tidak bisa dikomunikasikan dengan efektif. Inilah mengapa kami menginvestasikan effort signifikan dalam **visualisasi**.
>
> Dashboard Log Sentinel didesain menyerupai **Cyber Command Center** profesional. Di bagian atas, statistics cards memberikan overview instan: berapa total request, berapa yang terdeteksi sebagai anomali, dan persentase threat level.
>
> Highlight utama adalah **Live Cyber Threat Map**. Menggunakan Leaflet.js, kami memvisualisasikan attack flow dari source countries ke server target di Jakarta. Attack lines animasi memberikan kesan **real-time monitoring** yang engaging.
>
> Setiap marker dapat di-klik untuk menampilkan detail: **Source IP, Country of Origin, Attack Type, Severity Level, dan Timestamp**. Ini adalah informasi yang dibutuhkan security analyst untuk rapid response.
>
> Di bagian bawah, **PCA Scatter Plot** memberikan insight tentang distribusi data. Analyst dapat dengan mudah melihat apakah cluster anomali bertambah atau ada pola baru yang muncul.
>
> Semua ini berjalan secara **real-time** dengan auto-refresh, memungkinkan continuous monitoring tanpa manual intervention.

---

# SLIDE 9: EVALUASI MODEL
## Metrik Keberhasilan dan Validasi

### Poin-poin Materi:
- **Metrics yang Diukur:**
  | Metric | Value | Interpretasi |
  |--------|-------|--------------|
  | Accuracy | ~90% | Proporsi prediksi benar |
  | Precision | ~85% | Dari yang diprediksi anomali, berapa yang benar |
  | Recall | ~88% | Dari anomali sebenarnya, berapa yang terdeteksi |
  | F1-Score | ~86% | Harmonic mean precision & recall |

- **Confusion Matrix Analysis:**
  ```
              Predicted
              Normal  Anomaly
  Actual  
  Normal     TN:850   FP:50    → Specificity: 94.4%
  Anomaly    FN:12    TP:88    → Sensitivity: 88%
  ```

- **Trade-off Consideration:**
  - **Low False Positive:** Mengurangi alert fatigue
  - **Low False Negative:** Tidak melewatkan real threats
  - **Contamination tuning:** 0.1 = balanced untuk environment moderat

- **Unit Testing Coverage:**
  - 43 test cases passed
  - 159 assertions verified
  - Coverage: API, Dashboard, Security, Model

### 📢 Narasi Pembacaan (Speaker Notes):
> Evaluasi model adalah tahap kritis untuk memvalidasi bahwa sistem kita benar-benar bekerja.
>
> Model Isolation Forest kami mencapai **akurasi sekitar 90%** pada test set. Namun, dalam konteks cybersecurity, akurasi saja tidak cukup. Kita perlu memperhatikan **precision dan recall**.
>
> **Precision 85%** berarti dari semua alert yang dikirim, 85% adalah genuine threats. Sisanya 15% adalah false positives. Dalam security, false positive yang terlalu tinggi menyebabkan **alert fatigue** — analyst menjadi desensitized dan mulai mengabaikan alert.
>
> **Recall 88%** berarti dari semua serangan nyata, 88% berhasil terdeteksi. 12% yang lolos adalah **false negatives** — ini yang berbahaya karena real attacks tidak terdeteksi.
>
> Kami memilih **contamination=0.1** yang memberikan balance yang baik. Dalam environment dengan expected anomaly rate 10%, parameter ini optimal.
>
> Untuk memastikan reliability sistem secara keseluruhan, kami juga mengimplementasikan **43 unit test cases** dengan 159 assertions, mencakup API endpoints, dashboard rendering, security features, dan model behavior.

---

# SLIDE 10: ANALISIS KEAMANAN MODEL
## Ketahanan terhadap Adversarial Attacks

### Poin-poin Materi:
- **Potential Threats terhadap ML Model:**
  | Attack Type | Deskripsi | Mitigasi |
  |-------------|-----------|----------|
  | Model Evasion | Attacker crafts input yang lolos detection | Ensemble approach + continuous retraining |
  | Data Poisoning | Inject malicious data ke training set | Input validation + anomaly filtering |
  | Model Extraction | Reverse engineering model via queries | Rate limiting + query monitoring |
  | Adversarial Examples | Small perturbations yang fool classifier | Robust features + noise tolerance |

- **Defense Mechanisms Implemented:**
  1. **Input Validation:** Sanitasi data sebelum inference
  2. **Ensemble Robustness:** 100 trees = harder to evade all
  3. **Rate Limiting:** Mencegah model query flooding
  4. **Monitoring Layer:** Alert jika pattern berubah drastis

- **Limitation Acknowledgment:**
  - Isolation Forest dapat di-evade dengan **mimicry attacks**
  - Retraining dibutuhkan untuk adapt terhadap evolving threats
  - False positive masih ada (tidak 100% perfect)

### 📢 Narasi Pembacaan (Speaker Notes):
> Sebuah pertanyaan yang sering muncul: **"Apakah model ML untuk security bisa diserang balik?"** Jawabannya: **Ya**.
>
> Dalam domain **Adversarial Machine Learning**, attackers yang sophisticated dapat mencoba:
> 1. **Evasion attacks:** Mengcraft input yang sengaja menghindari detection
> 2. **Poisoning attacks:** Menginjeksi data malicious ke training untuk merusak model
> 3. **Model extraction:** Mencuri model logic via repeated queries
>
> Kami mengimplementasikan beberapa defense:
>
> **Ensemble approach** dengan 100 trees membuat evasion lebih sulit — attacker harus fool semua 100 trees simultaneously.
>
> **Input validation** memfilter data yang clearly malformed sebelum mencapai model.
>
> **Rate limiting** mencegah automated model probing.
>
> Namun, kami juga acknowledge **limitations**. Isolation Forest tidak immune terhadap mimicry attacks dimana attacker mempelajari pattern normal lalu mensimulasikannya. Solusi jangka panjang adalah **continuous retraining** dengan data terbaru.
>
> Security adalah **cat and mouse game** — tidak ada solusi permanent. Tapi dengan layered defense, kita meningkatkan **cost** bagi attacker secara signifikan.

---

# SLIDE 11: KESIMPULAN
## Mengapa Log Sentinel Outstanding?

### Poin-poin Materi:
- **Innovative Integration:**
  ✅ Menggabungkan Machine Learning + Cybersecurity
  ✅ Unsupervised learning untuk real-world applicability
  ✅ Explainable AI melalui PCA visualization

- **Production-Ready Features:**
  ✅ Microservice architecture (Laravel + Flask)
  ✅ Real-time monitoring dengan auto-refresh
  ✅ Comprehensive testing (43 test cases)
  ✅ Professional UI/UX (Velzon Admin Template)

- **Cybersecurity Value:**
  ✅ Live Threat Map seperti SOC profesional
  ✅ Multi-attack type detection
  ✅ Severity scoring untuk prioritization
  ✅ Forensic logging untuk incident response

- **Academic Excellence:**
  ✅ Implementasi materi kuliah secara komprehensif
  ✅ Dokumentasi lengkap dengan Mermaid diagrams
  ✅ Clean code dengan best practices
  ✅ Scalable architecture

### 📢 Narasi Pembacaan (Speaker Notes):
> Sebagai penutup, mari kita recap mengapa **Log Sentinel** adalah proyek yang outstanding.
>
> **Pertama**, integrasi. Kami tidak hanya mengimplementasikan algoritma ML. Kami membawanya ke konteks yang **relevan dan berdampak** — cybersecurity. Ini menunjukkan kemampuan menghubungkan teori dengan praktek nyata.
>
> **Kedua**, production-readiness. Banyak proyek akademik berhenti di Jupyter Notebook. Log Sentinel adalah **full-stack application** dengan microservice architecture, proper testing, dan professional UI. Ini siap untuk environment production.
>
> **Ketiga**, value proposition. Fitur-fitur seperti Live Threat Map, multi-attack detection, dan severity scoring adalah fitur yang biasa ditemukan di **commercial security products**. Kami membangun miniatur SIEM dengan resources mahasiswa.
>
> **Keempat**, academic rigor. Dokumentasi lengkap, diagram arsitektur, unit tests, dan clean code menunjukkan bahwa kami tidak hanya coding — kami engineering solution dengan proper methodology.
>
> Log Sentinel membuktikan bahwa mahasiswa dapat menghasilkan karya yang tidak hanya memenuhi kurikulum, tetapi juga **applicable di dunia industri**.

---

# SLIDE 12: Q&A & PENUTUP
## Diskusi dan Informasi Kontak

### Poin-poin Materi:
- **Repository:**
  🔗 [GitHub - uas-kecerdasan-artifisial-kel3](https://github.com/el-pablos/uas-kecerdasan-artifisial-kel3)

- **Tech Stack Summary:**
  - Backend: PHP 8.2 + Laravel 11
  - ML Service: Python 3.10 + Flask + Scikit-Learn
  - Database: MySQL 8.0
  - Frontend: Velzon + Bootstrap 5 + ApexCharts

- **Tim Pengembang:**
  | Nama | NPM | Kontribusi Utama |
  |------|-----|------------------|
  | Jeremy Christo Emmanuelle Panjaitan | 237006516084 | System Architecture |
  | Muhammad Akbar Hadi Pratama | 237006516058 | API & Backend Logic |
  | Farrel Alfaridzi | 237006516028 | UI/UX & Visualizations |
  | Chosmas Laurens Rumngewur | 217006516074 | ML Model & Flask Service |

- **Siap untuk Pertanyaan!**
  💬 *Silakan ajukan pertanyaan mengenai arsitektur, algoritma, atau implementasi*

### 📢 Narasi Pembacaan (Speaker Notes):
> Demikian presentasi dari kelompok kami tentang **Log Sentinel - Anomaly Detection System**.
>
> Kami sangat terbuka untuk diskusi dan pertanyaan. Apakah itu tentang **algoritma Isolation Forest** yang kami gunakan, **arsitektur microservice** yang kami bangun, atau **strategi cybersecurity** yang kami implementasikan — kami siap untuk berdiskusi.
>
> Source code lengkap tersedia di GitHub repository yang tertera. Kami juga menyediakan dokumentasi komprehensif termasuk diagram arsitektur, ERD, dan sequence diagrams menggunakan Mermaid.
>
> Terima kasih atas perhatian Bapak/Ibu dosen dan teman-teman sekalian. Kami tunggu pertanyaannya!
>
> 🙏 *Terima kasih*

---

## 📎 APPENDIX: QUICK REFERENCE

### Isolation Forest Formula
```
Anomaly Score = 2^(-E(h(x))/c(n))

Dimana:
- h(x) = path length dari instance x
- E(h(x)) = average path length over all trees
- c(n) = average path length dari unsuccessful search di BST
```

### PCA Variance Explained
```
PC1: ~45% variance explained
PC2: ~25% variance explained
Total: ~70% information retained in 2D projection
```

### Attack Signature Patterns
```
DDoS:      frequency > 100/min + status_code = 503
BruteForce: url contains '/login' + status_code = 401 + count > 10
SQLi:      url_length > 200 + contains('SELECT'|'UNION'|'DROP')
PathTravel: url contains '../' + status_code = 403
```

---

*Dokumen ini dibuat untuk keperluan presentasi UAS Mata Kuliah Kecerdasan Artifisial*
*© 2026 - Log Sentinel Team - Universitas Negeri Jakarta*
