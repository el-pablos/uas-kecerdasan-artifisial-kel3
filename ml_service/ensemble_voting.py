"""
========================================
ENSEMBLE VOTING MODULE
Multi-Model Consensus Anomaly Detection
========================================

Paper Reference:
"Reducing Single-Algorithm Bias in Web Server Anomaly Detection 
through Ensemble Voting Mechanism"

Modul ini mengimplementasikan arsitektur ensemble yang menggabungkan
tiga algoritma deteksi anomali: Isolation Forest, One-Class SVM, 
dan Local Outlier Factor untuk meningkatkan akurasi dan mengurangi
false positives/negatives.

Consensus Logic:
- CRITICAL (Merah): Semua model detect anomaly
- HIGH (Oranye): 2 dari 3 model detect anomaly  
- SUSPICIOUS (Kuning): 1 model detect anomaly
- NORMAL (Hijau): Tidak ada model detect anomaly

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
"""

import numpy as np
import pandas as pd
from sklearn.ensemble import IsolationForest
from sklearn.svm import OneClassSVM
from sklearn.neighbors import LocalOutlierFactor
from typing import Dict, List, Tuple, Optional, Any
from dataclasses import dataclass
from enum import Enum
import joblib
import os


class ThreatLevel(Enum):
    """Enum untuk level ancaman berdasarkan consensus voting."""
    NORMAL = "normal"
    SUSPICIOUS = "suspicious"
    HIGH = "high"
    CRITICAL = "critical"


@dataclass
class ModelPrediction:
    """Data class untuk menyimpan hasil prediksi dari satu model."""
    model_name: str
    prediction: int  # 1 = normal, -1 = anomaly
    score: float
    confidence: float


@dataclass
class EnsembleResult:
    """Data class untuk hasil ensemble voting."""
    threat_level: ThreatLevel
    consensus_score: float
    individual_predictions: List[ModelPrediction]
    voting_breakdown: Dict[str, bool]
    explanation: str


class EnsembleVotingClassifier:
    """
    Ensemble Voting Classifier untuk Anomaly Detection.
    
    Menggabungkan tiga model:
    1. Isolation Forest - Tree-based isolation
    2. One-Class SVM - Kernel-based boundary
    3. Local Outlier Factor - Density-based detection
    
    Keuntungan ensemble:
    - Mengurangi bias algoritma tunggal
    - Meningkatkan robustness terhadap noise
    - Memberikan confidence level melalui voting
    """
    
    def __init__(
        self,
        contamination: float = 0.1,
        random_state: int = 42,
        n_jobs: int = -1
    ):
        """
        Inisialisasi Ensemble Voting Classifier.
        
        Args:
            contamination: Proporsi outlier yang diharapkan (0.0 - 0.5)
            random_state: Random seed untuk reproducibility
            n_jobs: Jumlah CPU cores (-1 = semua)
        """
        self.contamination = contamination
        self.random_state = random_state
        self.n_jobs = n_jobs
        
        # Inisialisasi models
        self.models: Dict[str, Any] = {}
        self._initialize_models()
        
        # Status training
        self.is_fitted = False
        
        print("[INFO] EnsembleVotingClassifier initialized with 3 models")
    
    def _initialize_models(self) -> None:
        """Menginisialisasi ketiga model ensemble."""
        
        # Model 1: Isolation Forest
        # Kelebihan: Efisien untuk high-dimensional data
        self.models['isolation_forest'] = IsolationForest(
            n_estimators=100,
            contamination=self.contamination,
            max_samples='auto',
            random_state=self.random_state,
            n_jobs=self.n_jobs,
            bootstrap=False
        )
        
        # Model 2: One-Class SVM
        # Kelebihan: Robust terhadap outliers, kernel flexibility
        self.models['ocsvm'] = OneClassSVM(
            kernel='rbf',
            gamma='scale',
            nu=self.contamination,  # nu ≈ contamination
            cache_size=500
        )
        
        # Model 3: Local Outlier Factor
        # Kelebihan: Density-based, bagus untuk cluster-based anomalies
        # Note: LOF dengan novelty=True untuk prediksi data baru
        self.models['lof'] = LocalOutlierFactor(
            n_neighbors=20,
            contamination=self.contamination,
            novelty=True,  # PENTING: Untuk prediksi data baru
            n_jobs=self.n_jobs
        )
        
        print("[INFO] Models initialized: Isolation Forest, OCSVM, LOF")
    
    def fit(self, X: np.ndarray) -> 'EnsembleVotingClassifier':
        """
        Melatih semua model dengan data training.
        
        Args:
            X: Training data (normal samples)
        
        Returns:
            self
        """
        print(f"[INFO] Training ensemble with {len(X)} samples...")
        
        # Train Isolation Forest
        print("[INFO] Training Isolation Forest...")
        self.models['isolation_forest'].fit(X)
        
        # Train One-Class SVM
        print("[INFO] Training One-Class SVM...")
        self.models['ocsvm'].fit(X)
        
        # Train Local Outlier Factor
        print("[INFO] Training Local Outlier Factor...")
        self.models['lof'].fit(X)
        
        self.is_fitted = True
        print("[INFO] All models trained successfully!")
        
        return self
    
    def predict_single_model(
        self, 
        model_name: str, 
        X: np.ndarray
    ) -> ModelPrediction:
        """
        Mendapatkan prediksi dari satu model.
        
        Args:
            model_name: Nama model
            X: Data untuk diprediksi
        
        Returns:
            ModelPrediction object
        """
        model = self.models.get(model_name)
        if model is None:
            raise ValueError(f"Unknown model: {model_name}")
        
        # Prediksi
        prediction = model.predict(X)[0]
        
        # Score (decision function atau negative outlier factor)
        if hasattr(model, 'decision_function'):
            score = model.decision_function(X)[0]
        elif hasattr(model, 'score_samples'):
            score = model.score_samples(X)[0]
        else:
            score = 0.0
        
        # Hitung confidence dari score
        # Score semakin negatif = semakin yakin anomaly
        # Score semakin positif = semakin yakin normal
        confidence = self._score_to_confidence(score)
        
        return ModelPrediction(
            model_name=model_name,
            prediction=int(prediction),
            score=float(score),
            confidence=float(confidence)
        )
    
    def _score_to_confidence(self, score: float) -> float:
        """
        Mengkonversi score ke confidence level (0-1).
        
        Args:
            score: Raw score dari model
        
        Returns:
            Confidence level (0.0 - 1.0)
        """
        # Sigmoid-like transformation
        # Score berkisar sekitar -0.5 sampai 0.5 untuk IF
        confidence = 1 / (1 + np.exp(-score * 5))
        return round(min(1.0, max(0.0, confidence)), 4)
    
    def predict(self, X: np.ndarray) -> EnsembleResult:
        """
        Melakukan prediksi dengan ensemble voting.
        
        Args:
            X: Data untuk diprediksi (shape: 1 x n_features)
        
        Returns:
            EnsembleResult dengan consensus voting
        """
        if not self.is_fitted:
            raise RuntimeError("Ensemble must be fitted before prediction")
        
        # Pastikan X adalah 2D
        if X.ndim == 1:
            X = X.reshape(1, -1)
        
        # Dapatkan prediksi dari setiap model
        predictions: List[ModelPrediction] = []
        voting_breakdown: Dict[str, bool] = {}
        
        for model_name in self.models.keys():
            pred = self.predict_single_model(model_name, X)
            predictions.append(pred)
            voting_breakdown[model_name] = (pred.prediction == -1)  # True if anomaly
        
        # Hitung consensus
        anomaly_votes = sum(1 for v in voting_breakdown.values() if v)
        total_votes = len(voting_breakdown)
        
        # Tentukan threat level berdasarkan voting
        threat_level = self._determine_threat_level(anomaly_votes, total_votes)
        
        # Hitung consensus score (0-1, semakin tinggi = semakin yakin anomaly)
        consensus_score = self._calculate_consensus_score(predictions, anomaly_votes)
        
        # Generate explanation
        explanation = self._generate_explanation(
            threat_level, 
            anomaly_votes, 
            voting_breakdown, 
            predictions
        )
        
        return EnsembleResult(
            threat_level=threat_level,
            consensus_score=round(consensus_score, 4),
            individual_predictions=predictions,
            voting_breakdown=voting_breakdown,
            explanation=explanation
        )
    
    def _determine_threat_level(
        self, 
        anomaly_votes: int, 
        total_votes: int
    ) -> ThreatLevel:
        """
        Menentukan threat level berdasarkan jumlah vote anomaly.
        
        Args:
            anomaly_votes: Jumlah model yang vote anomaly
            total_votes: Total jumlah model
        
        Returns:
            ThreatLevel enum
        """
        if anomaly_votes == 0:
            return ThreatLevel.NORMAL
        elif anomaly_votes == 1:
            return ThreatLevel.SUSPICIOUS
        elif anomaly_votes == 2:
            return ThreatLevel.HIGH
        else:  # anomaly_votes >= 3
            return ThreatLevel.CRITICAL
    
    def _calculate_consensus_score(
        self, 
        predictions: List[ModelPrediction],
        anomaly_votes: int
    ) -> float:
        """
        Menghitung consensus score berdasarkan voting dan confidence.
        
        Args:
            predictions: List prediksi dari setiap model
            anomaly_votes: Jumlah vote anomaly
        
        Returns:
            Consensus score (0-1)
        """
        # Base score dari voting ratio
        voting_ratio = anomaly_votes / len(predictions)
        
        # Weighted average dari confidence untuk model yang vote anomaly
        anomaly_confidences = [
            p.confidence for p in predictions if p.prediction == -1
        ]
        
        if anomaly_confidences:
            avg_anomaly_confidence = np.mean(anomaly_confidences)
        else:
            avg_anomaly_confidence = 0.0
        
        # Combine voting ratio dan confidence
        consensus_score = (voting_ratio * 0.6) + (avg_anomaly_confidence * 0.4)
        
        return consensus_score
    
    def _generate_explanation(
        self,
        threat_level: ThreatLevel,
        anomaly_votes: int,
        voting_breakdown: Dict[str, bool],
        predictions: List[ModelPrediction]
    ) -> str:
        """
        Menghasilkan penjelasan untuk hasil ensemble.
        
        Args:
            threat_level: Level ancaman
            anomaly_votes: Jumlah vote anomaly
            voting_breakdown: Breakdown voting per model
            predictions: Prediksi individual
        
        Returns:
            String penjelasan
        """
        model_display_names = {
            'isolation_forest': 'Isolation Forest',
            'ocsvm': 'One-Class SVM',
            'lof': 'Local Outlier Factor (LOF)'
        }
        
        if threat_level == ThreatLevel.NORMAL:
            return "Semua model menyatakan request ini NORMAL. Tidak terdeteksi pola anomali."
        
        # Daftar model yang vote anomaly
        anomaly_models = [
            model_display_names.get(k, k) 
            for k, v in voting_breakdown.items() if v
        ]
        
        if threat_level == ThreatLevel.SUSPICIOUS:
            return (
                f"PERHATIAN: {anomaly_models[0]} mendeteksi pola mencurigakan. "
                f"Satu model dari tiga menyatakan anomaly. Investigasi lebih lanjut disarankan."
            )
        elif threat_level == ThreatLevel.HIGH:
            return (
                f"PERINGATAN TINGGI: {' dan '.join(anomaly_models)} mendeteksi anomaly. "
                f"Dua dari tiga model menyatakan request ini berbahaya."
            )
        else:  # CRITICAL
            return (
                f"KRITIS: SEMUA MODEL ({', '.join(anomaly_models)}) sepakat bahwa "
                f"request ini adalah ANOMALY. Diperlukan tindakan segera."
            )
    
    def predict_proba(self, X: np.ndarray) -> Dict:
        """
        Mendapatkan probability-like scores untuk prediksi.
        
        Args:
            X: Data untuk diprediksi
        
        Returns:
            Dictionary dengan probabilitas per kelas
        """
        result = self.predict(X)
        
        # Konversi consensus_score ke probabilitas
        prob_anomaly = result.consensus_score
        prob_normal = 1 - prob_anomaly
        
        return {
            'normal': round(prob_normal, 4),
            'anomaly': round(prob_anomaly, 4),
            'threat_level': result.threat_level.value
        }
    
    def get_model_info(self) -> Dict:
        """
        Mendapatkan informasi tentang ensemble.
        
        Returns:
            Dictionary dengan info model
        """
        return {
            'ensemble_type': 'Voting Classifier',
            'n_models': len(self.models),
            'models': list(self.models.keys()),
            'contamination': self.contamination,
            'is_fitted': self.is_fitted,
            'voting_strategy': 'majority_vote',
            'threat_levels': [level.value for level in ThreatLevel]
        }
    
    def save(self, directory: str) -> None:
        """
        Menyimpan ensemble ke disk.
        
        Args:
            directory: Direktori untuk menyimpan
        """
        os.makedirs(directory, exist_ok=True)
        
        for name, model in self.models.items():
            path = os.path.join(directory, f'{name}.joblib')
            joblib.dump(model, path)
        
        print(f"[INFO] Ensemble saved to {directory}")
    
    def load(self, directory: str) -> 'EnsembleVotingClassifier':
        """
        Memuat ensemble dari disk.
        
        Args:
            directory: Direktori tempat model disimpan
        
        Returns:
            self
        """
        for name in self.models.keys():
            path = os.path.join(directory, f'{name}.joblib')
            if os.path.exists(path):
                self.models[name] = joblib.load(path)
        
        self.is_fitted = True
        print(f"[INFO] Ensemble loaded from {directory}")
        
        return self


def create_ensemble_classifier(
    contamination: float = 0.1,
    random_state: int = 42
) -> EnsembleVotingClassifier:
    """
    Factory function untuk membuat EnsembleVotingClassifier.
    
    Args:
        contamination: Proporsi outlier yang diharapkan
        random_state: Random seed
    
    Returns:
        EnsembleVotingClassifier instance
    """
    return EnsembleVotingClassifier(
        contamination=contamination,
        random_state=random_state
    )


# ========================================
# UNIT TESTING
# ========================================

if __name__ == '__main__':
    # Test ensemble
    np.random.seed(42)
    
    # Generate normal data
    X_normal = np.random.randn(200, 6) * 0.5 + 5
    
    # Create and train ensemble
    ensemble = EnsembleVotingClassifier(contamination=0.1)
    ensemble.fit(X_normal)
    
    # Test with normal sample
    X_test_normal = np.array([[5.1, 4.9, 5.2, 5.0, 4.8, 5.1]])
    result_normal = ensemble.predict(X_test_normal)
    
    print("\n=== Normal Sample Test ===")
    print(f"Threat Level: {result_normal.threat_level.value}")
    print(f"Consensus Score: {result_normal.consensus_score}")
    print(f"Explanation: {result_normal.explanation}")
    
    # Test with anomaly sample
    X_test_anomaly = np.array([[100, -50, 20, 1000, 500, 50]])
    result_anomaly = ensemble.predict(X_test_anomaly)
    
    print("\n=== Anomaly Sample Test ===")
    print(f"Threat Level: {result_anomaly.threat_level.value}")
    print(f"Consensus Score: {result_anomaly.consensus_score}")
    print(f"Voting: {result_anomaly.voting_breakdown}")
    print(f"Explanation: {result_anomaly.explanation}")
    
    # Model info
    print("\n=== Ensemble Info ===")
    print(ensemble.get_model_info())
