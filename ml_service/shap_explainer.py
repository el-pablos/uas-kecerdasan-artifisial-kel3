"""
========================================
SHAP EXPLAINER MODULE
Explainable AI menggunakan SHapley Additive exPlanations
========================================

Paper Reference:
"Enhancing Interpretability of Isolation Forest in Web Server 
Attack Detection using SHAP Values"

Modul ini mengubah model "Black Box" Isolation Forest menjadi
sistem yang dapat dijelaskan (Explainable AI). Setiap prediksi
anomali akan disertai dengan penjelasan kontribusi setiap fitur.

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

import shap
import numpy as np
import pandas as pd
from typing import Dict, List, Tuple, Optional, Any
from sklearn.ensemble import IsolationForest
import warnings

# Suppress SHAP warnings untuk cleaner output
warnings.filterwarnings('ignore', category=UserWarning, module='shap')


class SHAPExplainer:
    """
    Kelas untuk menjelaskan prediksi Isolation Forest menggunakan SHAP values.
    
    SHAP (SHapley Additive exPlanations) adalah metode untuk menjelaskan
    output dari machine learning model. Untuk setiap prediksi, SHAP memberikan
    kontribusi setiap fitur terhadap keputusan model.
    
    Contoh output:
    "Alert dipicu karena 'request_count_per_minute' = 5000 berkontribusi +0.35
     terhadap skor anomali, jauh melebihi threshold normal 100."
    """
    
    # Nama fitur untuk penjelasan yang lebih readable
    FEATURE_NAMES = [
        'ip_numeric',
        'method_encoded',
        'status_code',
        'response_time',
        'url_length',
        'user_agent_idx'
    ]
    
    # Nama fitur dalam bahasa manusia
    FEATURE_DESCRIPTIONS = {
        'ip_numeric': 'IP Address (numeric)',
        'method_encoded': 'HTTP Method',
        'status_code': 'Status Code',
        'response_time': 'Response Time (ms)',
        'url_length': 'URL Length (chars)',
        'user_agent_idx': 'User Agent Type'
    }
    
    # Threshold normal untuk setiap fitur (untuk konteks penjelasan)
    NORMAL_THRESHOLDS = {
        'ip_numeric': {'min': 1, 'max': 255, 'unit': ''},
        'method_encoded': {'min': 0, 'max': 6, 'unit': ''},
        'status_code': {'min': 200, 'max': 299, 'unit': ''},
        'response_time': {'min': 0, 'max': 500, 'unit': 'ms'},
        'url_length': {'min': 5, 'max': 100, 'unit': 'chars'},
        'user_agent_idx': {'min': 0, 'max': 7, 'unit': ''}
    }
    
    def __init__(self, model: IsolationForest, background_data: np.ndarray = None):
        """
        Inisialisasi SHAP Explainer untuk Isolation Forest.
        
        Args:
            model: Trained Isolation Forest model
            background_data: Background dataset untuk SHAP (opsional)
        """
        self.model = model
        self.background_data = background_data
        self.explainer = None
        self._initialize_explainer()
        
        print("[INFO] SHAPExplainer initialized successfully")
    
    def _initialize_explainer(self) -> None:
        """
        Menginisialisasi SHAP TreeExplainer untuk Isolation Forest.
        """
        try:
            # TreeExplainer bekerja langsung dengan tree-based models
            self.explainer = shap.TreeExplainer(
                self.model,
                feature_perturbation='interventional'
            )
            print("[INFO] TreeExplainer created for Isolation Forest")
        except Exception as e:
            print(f"[WARN] TreeExplainer failed, using KernelExplainer: {e}")
            # Fallback ke KernelExplainer jika TreeExplainer gagal
            if self.background_data is not None:
                self.explainer = shap.KernelExplainer(
                    self.model.decision_function,
                    shap.sample(self.background_data, 100)
                )
    
    def calculate_shap_values(self, features: np.ndarray) -> Tuple[np.ndarray, float]:
        """
        Menghitung SHAP values untuk satu instance prediksi.
        
        Args:
            features: Numpy array dengan fitur (shape: 1 x n_features)
        
        Returns:
            Tuple of (shap_values array, base_value)
        """
        if self.explainer is None:
            raise RuntimeError("SHAP Explainer not initialized")
        
        # Pastikan features adalah 2D
        if features.ndim == 1:
            features = features.reshape(1, -1)
        
        # Hitung SHAP values
        shap_values = self.explainer.shap_values(features)
        
        # Handle berbagai format output SHAP
        if isinstance(shap_values, list):
            shap_values = shap_values[0]
        
        # Base value (expected value)
        base_value = float(self.explainer.expected_value)
        if isinstance(base_value, np.ndarray):
            base_value = float(base_value[0])
        
        return shap_values[0] if shap_values.ndim > 1 else shap_values, base_value
    
    def get_feature_contributions(
        self, 
        features: np.ndarray, 
        feature_values: Dict = None
    ) -> List[Dict]:
        """
        Mendapatkan kontribusi setiap fitur terhadap prediksi.
        
        Args:
            features: Numpy array dengan fitur
            feature_values: Dictionary dengan nilai asli fitur (opsional)
        
        Returns:
            List of dictionaries dengan kontribusi setiap fitur
        """
        shap_values, base_value = self.calculate_shap_values(features)
        
        contributions = []
        for i, (name, shap_val) in enumerate(zip(self.FEATURE_NAMES, shap_values)):
            contribution = {
                'feature_name': name,
                'feature_description': self.FEATURE_DESCRIPTIONS.get(name, name),
                'shap_value': round(float(shap_val), 6),
                'contribution_direction': 'anomaly' if shap_val > 0 else 'normal',
                'contribution_strength': self._get_contribution_strength(shap_val),
                'feature_value': float(features[0, i]) if features.ndim > 1 else float(features[i])
            }
            
            # Tambahkan context apakah nilai abnormal
            threshold = self.NORMAL_THRESHOLDS.get(name, {})
            if threshold:
                contribution['is_abnormal'] = self._is_value_abnormal(
                    contribution['feature_value'], 
                    threshold
                )
                contribution['normal_range'] = f"{threshold.get('min', 0)}-{threshold.get('max', 100)}"
            
            contributions.append(contribution)
        
        # Sort by absolute SHAP value (most important first)
        contributions.sort(key=lambda x: abs(x['shap_value']), reverse=True)
        
        return contributions
    
    def _get_contribution_strength(self, shap_value: float) -> str:
        """
        Mengkategorikan kekuatan kontribusi berdasarkan nilai SHAP.
        
        Args:
            shap_value: Nilai SHAP
        
        Returns:
            String kategori kekuatan
        """
        abs_val = abs(shap_value)
        
        if abs_val < 0.01:
            return 'negligible'
        elif abs_val < 0.05:
            return 'weak'
        elif abs_val < 0.15:
            return 'moderate'
        elif abs_val < 0.3:
            return 'strong'
        else:
            return 'very_strong'
    
    def _is_value_abnormal(self, value: float, threshold: Dict) -> bool:
        """
        Mengecek apakah nilai fitur berada di luar range normal.
        
        Args:
            value: Nilai fitur
            threshold: Dictionary dengan 'min' dan 'max'
        
        Returns:
            True jika abnormal
        """
        min_val = threshold.get('min', float('-inf'))
        max_val = threshold.get('max', float('inf'))
        return value < min_val or value > max_val
    
    def generate_explanation(
        self, 
        features: np.ndarray,
        prediction: int,
        log_data: Dict = None
    ) -> Dict:
        """
        Menghasilkan penjelasan lengkap untuk satu prediksi.
        
        Args:
            features: Numpy array dengan fitur
            prediction: Hasil prediksi (1=normal, -1=anomaly)
            log_data: Data log original (opsional)
        
        Returns:
            Dictionary dengan penjelasan lengkap
        """
        shap_values, base_value = self.calculate_shap_values(features)
        contributions = self.get_feature_contributions(features, log_data)
        
        # Tentukan top contributors
        top_contributors = contributions[:3]  # Top 3 contributors
        
        # Generate human-readable explanation
        explanation_text = self._generate_text_explanation(
            prediction, 
            top_contributors
        )
        
        # Calculate overall contribution sum
        total_shap = sum(abs(c['shap_value']) for c in contributions)
        
        return {
            'prediction': 'anomaly' if prediction == -1 else 'normal',
            'prediction_code': int(prediction),
            'base_value': round(base_value, 6),
            'total_shap_contribution': round(total_shap, 6),
            'explanation_text': explanation_text,
            'top_contributors': top_contributors,
            'all_contributions': contributions,
            'feature_importance_ranking': [c['feature_name'] for c in contributions]
        }
    
    def _generate_text_explanation(
        self, 
        prediction: int, 
        top_contributors: List[Dict]
    ) -> str:
        """
        Menghasilkan penjelasan dalam bahasa manusia.
        
        Args:
            prediction: Hasil prediksi
            top_contributors: Top 3 kontributor
        
        Returns:
            String penjelasan
        """
        if prediction == 1:
            return "Request diklasifikasikan sebagai NORMAL. Tidak ada fitur yang menunjukkan pola anomali signifikan."
        
        # Prediksi anomaly
        parts = ["Request terdeteksi sebagai ANOMALY."]
        
        for i, contrib in enumerate(top_contributors, 1):
            feature = contrib['feature_description']
            value = contrib['feature_value']
            strength = contrib['contribution_strength']
            direction = contrib['contribution_direction']
            
            if direction == 'anomaly' and strength in ['strong', 'very_strong', 'moderate']:
                parts.append(
                    f"({i}) '{feature}' = {value:.1f} berkontribusi signifikan terhadap deteksi anomali"
                )
        
        if len(parts) == 1:
            parts.append("Kombinasi beberapa fitur minor yang secara kumulatif mengindikasikan anomali.")
        
        return " ".join(parts)
    
    def get_global_feature_importance(
        self, 
        dataset: np.ndarray,
        sample_size: int = 100
    ) -> List[Dict]:
        """
        Menghitung global feature importance dari dataset.
        
        Args:
            dataset: Dataset untuk analisis
            sample_size: Jumlah sample untuk efisiensi
        
        Returns:
            List of feature importance
        """
        # Sample data jika terlalu besar
        if len(dataset) > sample_size:
            indices = np.random.choice(len(dataset), sample_size, replace=False)
            sampled_data = dataset[indices]
        else:
            sampled_data = dataset
        
        # Hitung SHAP values untuk semua samples
        shap_values = self.explainer.shap_values(sampled_data)
        
        if isinstance(shap_values, list):
            shap_values = shap_values[0]
        
        # Hitung mean absolute SHAP value per fitur
        mean_abs_shap = np.mean(np.abs(shap_values), axis=0)
        
        # Buat ranking
        importance_list = []
        for i, name in enumerate(self.FEATURE_NAMES):
            importance_list.append({
                'feature_name': name,
                'feature_description': self.FEATURE_DESCRIPTIONS.get(name, name),
                'importance_score': round(float(mean_abs_shap[i]), 6),
                'importance_percentage': round(float(mean_abs_shap[i] / sum(mean_abs_shap) * 100), 2)
            })
        
        # Sort by importance
        importance_list.sort(key=lambda x: x['importance_score'], reverse=True)
        
        return importance_list


# Factory function untuk membuat explainer
def create_shap_explainer(
    model: IsolationForest, 
    background_data: np.ndarray = None
) -> SHAPExplainer:
    """
    Factory function untuk membuat SHAP Explainer.
    
    Args:
        model: Trained Isolation Forest model
        background_data: Background data untuk SHAP
    
    Returns:
        SHAPExplainer instance
    """
    return SHAPExplainer(model, background_data)


# ========================================
# UNIT TESTING
# ========================================

if __name__ == '__main__':
    from sklearn.ensemble import IsolationForest
    
    # Create dummy model dan data
    np.random.seed(42)
    X_train = np.random.randn(100, 6)
    
    model = IsolationForest(n_estimators=50, contamination=0.1, random_state=42)
    model.fit(X_train)
    
    # Test SHAP Explainer
    explainer = SHAPExplainer(model, X_train)
    
    # Test instance
    test_instance = np.array([[0.5, 1, 200, 150, 50, 2]])
    
    # Get explanation
    explanation = explainer.generate_explanation(
        test_instance,
        prediction=model.predict(test_instance)[0]
    )
    
    print("\n=== SHAP Explanation ===")
    print(f"Prediction: {explanation['prediction']}")
    print(f"Explanation: {explanation['explanation_text']}")
    print(f"\nTop Contributors:")
    for contrib in explanation['top_contributors']:
        print(f"  - {contrib['feature_description']}: SHAP={contrib['shap_value']:.4f}")
